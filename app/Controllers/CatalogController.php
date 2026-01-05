<?php

namespace App\Controllers;

use App\Models\CatalogModel;
use App\Models\CatalogFileModel;
use App\Libraries\DocxParser;
use App\Services\AiAnalysisService;
use App\Services\PaymentService;

class CatalogController extends BaseController
{
    protected $helpers = ['form', 'text'];

    public function create()
    {
        return view('catalog/create');
    }

    public function analyze($id)
    {
        // Simple security check: User must own the catalog
        $catalogModel = new CatalogModel();
        $catalog = $catalogModel->find($id);

        if (! $catalog || $catalog['user_id'] != session()->get('id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $service = new AiAnalysisService();
        if ($service->analyzeContent($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Analysis completed']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Analysis failed'])->setStatusCode(500);
        }
    }

    public function view($id)
    {
        // Alias for review for now, or read-only view
        return $this->review($id);
    }

    public function review($id)
    {
        $catalogModel = new CatalogModel();
        $catalog = $catalogModel->find($id);

        if (! $catalog || $catalog['user_id'] != session()->get('id')) {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized');
        }

        $pageModel = new \App\Models\CatalogPageModel();
        $pages = $pageModel->where('catalog_id', $id)->orderBy('page_number', 'ASC')->findAll();

        $fileModel = new \App\Models\CatalogFileModel();
        $images = $fileModel->where('catalog_id', $id)->where('file_type', 'image')->findAll();

        // Create a map of image ID -> URL (relative path) for display
        $imageMap = [];
        foreach ($images as $img) {
            // Need to serve these files. For 'writable', we usually need a controller to serve them
            // OR symlink. For now, assuming we might need a route to serve 'writable/uploads' or copy them to public.
            // Best practice: serve via controller.
            $imageMap[$img['id']] = '/catalog/image/' . $img['id'];
        }

        return view('catalog/review', [
            'catalog' => $catalog,
            'pages' => $pages,
            'imageMap' => $imageMap,
            'images' => $images // Pass full list for manual selection if needed
        ]);
    }

    public function serveImage($fileId)
    {
        // Simple file server
        $fileModel = new \App\Models\CatalogFileModel();
        $file = $fileModel->find($fileId);

        // Security check: Check if user owns the catalog of this file
        $catalogModel = new CatalogModel();
        $catalog = $catalogModel->find($file['catalog_id']);
        if ($catalog['user_id'] != session()->get('id')) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (file_exists($file['file_path'])) {
            $mime = mime_content_type($file['file_path']);
            header('Content-Type: ' . $mime);
            readfile($file['file_path']);
            exit;
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    public function feedback()
    {
        if (! session()->get('isLoggedIn')) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $pageId = $this->request->getPost('page_id');
        $feedback = $this->request->getPost('user_feedback');

        // Security Check: Verify Ownership
        $pageModel = new \App\Models\CatalogPageModel();
        $page = $pageModel->find($pageId);

        if (!$page) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Page not found'])->setStatusCode(404);
        }

        $catalogModel = new CatalogModel();
        $catalog = $catalogModel->find($page['catalog_id']);

        if (!$catalog || $catalog['user_id'] != session()->get('id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(403);
        }

        $service = new AiAnalysisService();
        $result = $service->revisePage($pageId, $feedback);

        if ($result) {
            return $this->response->setJSON(['status' => 'success', 'data' => $result]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Revision failed'])->setStatusCode(500);
        }
    }

    public function approvePage()
    {
        if (! session()->get('isLoggedIn')) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $pageId = $this->request->getPost('page_id');
        $pageModel = new \App\Models\CatalogPageModel();

        // Security Check: Verify Ownership
        $page = $pageModel->find($pageId);
        if (!$page) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Page not found'])->setStatusCode(404);
        }

        $catalogModel = new CatalogModel();
        $catalog = $catalogModel->find($page['catalog_id']);

        if (!$catalog || $catalog['user_id'] != session()->get('id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(403);
        }

        if ($pageModel->update($pageId, ['status' => 'approved'])) {
            // Check if all pages are approved
            $pendingCount = $pageModel->where('catalog_id', $page['catalog_id'])
                                      ->where('status !=', 'approved')
                                      ->countAllResults();

            $allApproved = ($pendingCount == 0);

            if ($allApproved) {
                // Do NOT mark as completed yet. We need payment.
                // Just notify frontend that all are approved so it can show the "Finalize & Pay" button.
            }

            return $this->response->setJSON(['status' => 'success', 'all_approved' => $allApproved]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Update failed'])->setStatusCode(500);
    }

    public function finalize($catalogId)
    {
        if (! session()->get('isLoggedIn')) {
             return redirect()->to('/login');
        }

        $catalogModel = new CatalogModel();
        $catalog = $catalogModel->find($catalogId);

        if (!$catalog || $catalog['user_id'] != session()->get('id')) {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized');
        }

        $paymentService = new PaymentService();
        $result = $paymentService->deductForCatalog(session()->get('id'), $catalogId);

        if ($result['success']) {
            // Payment successful, mark for rendering
            $catalogModel->update($catalogId, ['status' => 'completed']); // QueueController picks 'approved' pages.
            // Wait, QueueController picks 'approved' pages regardless of Catalog status in previous implementation.
            // If we want to Gate rendering by Payment, we should have a 'paid' flag or status.
            // Let's assume QueueController only processes if Catalog is 'paid' or 'completed'.
            // I need to update QueueController check.

            return redirect()->to('/dashboard')->with('success', 'پرداخت موفق بود. کاتالوگ شما در صف ساخت قرار گرفت.');
        } else {
            return redirect()->to('/payment/charge')->with('error', 'موجودی کافی نیست. لطفاً کیف پول خود را شارژ کنید. هزینه: ' . number_format($result['required']) . ' تومان');
        }
    }

    public function sse($catalogId)
    {
        // Simple SSE to notify frontend of "rendering started" or similar
        // For now, we just stream status
        $catalogModel = new CatalogModel();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        // Initial check
        $catalog = $catalogModel->find($catalogId);
        echo "data: " . json_encode(['status' => $catalog['status']]) . "\n\n";
        ob_flush();
        flush();

        // In a real app, we would loop and sleep, but PHP timeouts apply.
        // For this demo, sending once is enough to verify connection,
        // or frontend can poll. SSE in PHP-FPM is tricky.
        // Let's output and exit to not block worker if not configured properly.
        exit;
    }

    public function uploadFiles()
    {
        // Check if user is logged in (AuthFilter should handle this, but double check)
        if (! session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        // Validation
        $validationRule = [
            'title' => 'required|min_length[3]',
            'docx_file' => [
                'label' => 'DOCX File',
                'rules' => 'uploaded[docx_file]|max_size[docx_file,10240]|ext_in[docx_file,docx]'
            ],
            'images' => [
                'label' => 'Images',
                'rules' => 'uploaded[images]|max_size[images,5120]|ext_in[images,jpg,jpeg,png]|max_dims[images,4096,4096]'
            ]
        ];

        if (! $this->validate($validationRule)) {
             return $this->response->setJSON(['status' => 'error', 'errors' => $this->validator->getErrors()])->setStatusCode(400);
        }

        $userId = session()->get('id');
        $title = $this->request->getPost('title');

        // 1. Create Catalog Record
        $catalogModel = new CatalogModel();
        $catalogData = [
            'user_id' => $userId,
            'title' => $title,
            'status' => 'processing',
            'total_pages' => 0,
            'total_cost' => 0
        ];

        $catalogId = $catalogModel->insert($catalogData);
        if (! $catalogId) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create catalog record'])->setStatusCode(500);
        }

        // 2. Setup Upload Directory
        $uploadPath = WRITEPATH . 'uploads/' . $catalogId . '/';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $fileModel = new CatalogFileModel();
        $fileIds = [];
        $docxPath = '';

        // 3. Handle DOCX
        $docxFile = $this->request->getFile('docx_file');
        if ($docxFile->isValid() && ! $docxFile->hasMoved()) {
            $newName = $docxFile->getRandomName();
            $docxFile->move($uploadPath, $newName);
            $docxPath = $uploadPath . $newName;

            $fileId = $fileModel->insert([
                'catalog_id' => $catalogId,
                'file_type' => 'docx',
                'file_path' => $docxPath,
                'original_name' => $docxFile->getClientName()
            ]);
            $fileIds[] = $fileId;
        }

        // 4. Handle Images
        $images = $this->request->getFileMultiple('images');
        if ($images) {
            foreach ($images as $img) {
                if ($img->isValid() && ! $img->hasMoved()) {
                    $newName = $img->getRandomName();
                    $img->move($uploadPath, $newName);

                    $fileId = $fileModel->insert([
                        'catalog_id' => $catalogId,
                        'file_type' => 'image',
                        'file_path' => $uploadPath . $newName,
                        'original_name' => $img->getClientName()
                    ]);
                    $fileIds[] = $fileId;
                }
            }
        }

        // 5. Extract Text from DOCX
        try {
            $parser = new DocxParser();
            $content = $parser->read($docxPath);
            // Save extracted content to a file for Phase 4
            file_put_contents($uploadPath . 'content.txt', $content);
        } catch (\Exception $e) {
            // Log error but don't fail the upload entirely
            log_message('error', 'DOCX Extraction failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'status' => 'success',
            'catalog_id' => $catalogId,
            'file_ids' => $fileIds,
            'message' => 'Files uploaded successfully'
        ]);
    }
}
