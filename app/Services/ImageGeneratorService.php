<?php

namespace App\Services;

use App\Models\CatalogModel;
use App\Models\CatalogPageModel;
use CodeIgniter\I18n\Time;

class ImageGeneratorService
{
    private $apiBaseUrl = 'https://api.avalai.ir/v1';
    private $apiKey;
    private $model;
    private $client;
    private $width;
    private $height;
    private $quality;

    public function __construct()
    {
        $this->apiKey = getenv('AVALAI_API_KEY');
        $this->model = getenv('AI_IMAGE_MODEL') ?: 'imagen-4.0-generate-001';
        $this->width = getenv('IMAGE_WIDTH') ?: 1024;
        $this->height = getenv('IMAGE_HEIGHT') ?: 1024;
        $this->quality = getenv('IMAGE_QUALITY') ?: 'hd';
        $this->client = \Config\Services::curlrequest();
    }

    /**
     * Generates an image for a specific page.
     *
     * @param int $pageId
     * @return bool Success status
     */
    public function generatePage($pageId)
    {
        $pageModel = new CatalogPageModel();
        $catalogModel = new CatalogModel();

        $page = $pageModel->find($pageId);
        if (! $page) {
            return false;
        }

        $proposal = json_decode($page['ai_proposal'], true);
        $title = $proposal['title'] ?? '';
        $content = $proposal['content'] ?? '';
        $imageIds = implode(', ', $proposal['suggested_images'] ?? []);

        // Construct Prompt
        $prompt = "ساخت یک تصویر کاتالوگ حرفه‌ای با مشخصات زیر:

ابعاد: 16:9 (3840x2160 پیکسل برای چاپ با کیفیت)

محتوا:
عنوان: {$title}
متن اصلی: {$content}

طراحی:
- فونت فارسی واضح و خوانا
- رنگ‌بندی مدرن و چشم‌نواز
- قرار دادن متن در محل مناسب
- استفاده از تصاویر پیشنهادی: {$imageIds}

سبک: مدرن، حرفه‌ای، مناسب چاپ

مهم: تمام متن‌های فارسی باید کاملاً خوانا و بدون خطای تایپوگرافی باشه.";

        try {
            // Call API
            $response = $this->client->post($this->apiBaseUrl . '/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => "{$this->width}x{$this->height}",
                    'quality' => $this->quality,
                    'response_format' => 'url'
                ],
                'timeout' => 120 // Longer timeout for image generation
            ]);

            $body = json_decode($response->getBody(), true);
            $imageUrl = $body['data'][0]['url'] ?? null;

            if (! $imageUrl) {
                log_message('error', 'Image Generation API failed to return URL.');
                return false;
            }

            // Download Image
            $imageContent = file_get_contents($imageUrl);
            if (! $imageContent) {
                 log_message('error', 'Failed to download generated image.');
                 return false;
            }

            // Save to file
            $catalogId = $page['catalog_id'];
            $savePath = WRITEPATH . "uploads/{$catalogId}/";
            if (! is_dir($savePath)) {
                mkdir($savePath, 0777, true);
            }

            $filename = "page_{$page['page_number']}.png";
            $fullPath = $savePath . $filename;

            file_put_contents($fullPath, $imageContent);

            // Update Database
            $pageModel->update($pageId, [
                'image_path' => $fullPath,
                'status' => 'rendered',
                'updated_at' => Time::now()
            ]);

            return true;

        } catch (\Exception $e) {
            log_message('error', 'Image Generation Exception: ' . $e->getMessage());
            return false;
        }
    }
}
