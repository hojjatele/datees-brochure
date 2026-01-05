<?php

namespace App\Services;

use App\Models\CatalogModel;
use App\Models\CatalogFileModel;
use App\Models\CatalogPageModel;
use CodeIgniter\I18n\Time;

class AiAnalysisService
{
    private $apiBaseUrl = 'https://api.avalai.ir/v1';
    private $apiKey;
    private $model;
    private $client;

    public function __construct()
    {
        $this->apiKey = getenv('AVALAI_API_KEY');
        $this->model = getenv('AI_ANALYSIS_MODEL') ?: 'claude-3-5-sonnet-20241022';
        // We use basic curl if Guzzle is not fully available/configured, or we can use CI4's CURLRequest
        $this->client = \Config\Services::curlrequest();
    }

    /**
     * Analyzes content and generates a catalog proposal.
     *
     * @param int $catalogId
     * @return bool Success status
     */
    public function analyzeContent($catalogId)
    {
        $catalogModel = new CatalogModel();
        $fileModel = new CatalogFileModel();
        $pageModel = new CatalogPageModel();

        $catalog = $catalogModel->find($catalogId);
        if (!$catalog) {
            return false;
        }

        // 1. Read DOCX Content
        $contentPath = WRITEPATH . 'uploads/' . $catalogId . '/content.txt';
        $docxContent = '';
        if (file_exists($contentPath)) {
            $docxContent = file_get_contents($contentPath);
        } else {
            log_message('warning', "Content file not found for catalog $catalogId");
            $docxContent = "محتوای متنی یافت نشد.";
        }

        // 2. Get Images info
        $images = $fileModel->where('catalog_id', $catalogId)
                            ->where('file_type', 'image')
                            ->findAll();
        $imageCount = count($images);

        // Prepare image list for AI context
        $imageListObj = [];
        foreach ($images as $img) {
            $imageListObj[] = "ID: {$img['id']} (Name: {$img['original_name']})";
        }
        $imageListString = implode(", ", $imageListObj);

        // 3. Construct Prompt
        $prompt = "شما یک طراح حرفه‌ای کاتالوگ هستید. محتوای زیر را دریافت کرده‌اید:

متن فایل:
{$docxContent}

تعداد تصاویر موجود: {$imageCount}
لیست تصاویر (ID - نام): {$imageListString}

لطفاً یک پیشنهاد کامل برای یک کاتالوگ 6 صفحه‌ای ارائه دهید.

برای هر صفحه مشخص کنید:
1. عنوان صفحه
2. عناوین و متون کوتاه (حداکثر 200 کلمه)
3. تصاویر پیشنهادی (فقط از ID های لیست بالا استفاده کن)
4. چیدمان پیشنهادی (cover/header-body/body-footer/full-page)

خروجی را *فقط* به صورت JSON معتبر با این ساختار بده (بدون متن اضافه):
{
  \"total_pages\": 6,
  \"pages\": [
    {
      \"page_number\": 1,
      \"title\": \"کاور اصلی\",
      \"content\": \"متن پیشنهادی...\",
      \"suggested_images\": [12, 15],
      \"layout\": \"cover\"
    }
  ]
}";

        // 4. Send to AI
        try {
            $response = $this->client->post($this->apiBaseUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 4000,
                    'temperature' => 0.7
                ],
                'timeout' => 60
            ]);

            $body = json_decode($response->getBody(), true);
            $aiContent = $body['choices'][0]['message']['content'] ?? '';

            // Clean markdown json code blocks
            $jsonStr = $this->cleanJson($aiContent);
            $proposal = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! isset($proposal['pages'])) {
                log_message('error', 'Invalid JSON from AI: ' . $jsonStr);
                return false;
            }

            // 5. Save Pages
            $pageModel->where('catalog_id', $catalogId)->delete();

            foreach ($proposal['pages'] as $page) {
                $pageModel->insert([
                    'catalog_id' => $catalogId,
                    'page_number' => $page['page_number'],
                    'ai_proposal' => json_encode([
                        'title' => $page['title'],
                        'content' => $page['content'],
                        'suggested_images' => $page['suggested_images'] ?? [],
                        'layout' => $page['layout'] ?? 'body'
                    ], JSON_UNESCAPED_UNICODE),
                    'status' => 'pending',
                    'created_at' => Time::now(),
                ]);
            }

            // Update Catalog
            $catalogModel->update($catalogId, [
                'total_pages' => count($proposal['pages']),
                'status' => 'processing',
            ]);

            return true;

        } catch (\Exception $e) {
            log_message('error', 'AI Request Failed: ' . $e->getMessage());
            return false;
        }
    }

    private function cleanJson($text)
    {
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        return trim($text);
    }

    /**
     * Revises a specific page based on user feedback.
     *
     * @param int $pageId
     * @param string $userFeedback
     * @return array|false New page data or false on failure
     */
    public function revisePage($pageId, $userFeedback)
    {
        $pageModel = new CatalogPageModel();
        $page = $pageModel->find($pageId);

        if (! $page) {
            return false;
        }

        $currentProposal = json_decode($page['ai_proposal'], true);
        $currentContent = $currentProposal['content'] ?? '';
        $pageNumber = $page['page_number'];

        // Use a different model for revision if specified, or fallback to main model
        $revisionModel = getenv('AI_REVISION_MODEL') ?: 'gpt-4o';

        $prompt = "محتوای فعلی صفحه {$pageNumber}:
{$currentContent}

بازخورد کاربر:
{$userFeedback}

لطفاً محتوا را بر اساس بازخورد کاربر اصلاح کن.
خروجی را *فقط* به صورت JSON با ساختار زیر برگردان (بدون متن اضافه):
{
  \"title\": \"...\",
  \"content\": \"...\",
  \"suggested_images\": [...],
  \"layout\": \"...\"
}
نکته: layout باید یکی از مقادیر cover, header-body, body-footer, full-page باشد.
اگر کاربر خواسته تصویر تغییر کند، ID تصویر پیشنهادی را در suggested_images بگذار.";

        try {
            $response = $this->client->post($this->apiBaseUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $revisionModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.7
                ],
                'timeout' => 60
            ]);

            $body = json_decode($response->getBody(), true);
            $aiContent = $body['choices'][0]['message']['content'] ?? '';

            $jsonStr = $this->cleanJson($aiContent);
            $newProposal = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! isset($newProposal['content'])) {
                log_message('error', 'Invalid Revision JSON from AI: ' . $jsonStr);
                return false;
            }

            // Merge with existing data to ensure we don't lose fields if AI omits them (though we asked for full structure)
            $finalProposal = array_merge($currentProposal, $newProposal);

            // Update DB
            $pageModel->update($pageId, [
                'ai_proposal' => json_encode($finalProposal, JSON_UNESCAPED_UNICODE),
                'user_feedback' => $userFeedback, // Store last feedback
                // Status remains pending until approved
            ]);

            return $finalProposal;

        } catch (\Exception $e) {
            log_message('error', 'AI Revision Failed: ' . $e->getMessage());
            return false;
        }
    }
}
