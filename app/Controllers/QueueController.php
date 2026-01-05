<?php

namespace App\Controllers;

use App\Models\CatalogPageModel;
use App\Services\ImageGeneratorService;

class QueueController extends BaseController
{
    /**
     * Process pending approved pages.
     * Can be triggered via cron or AJAX loop.
     */
    public function process($limit = 1)
    {
        // Simple security: Allow CLI or authenticated admin/user trigger
        // Ideally this should be protected by a special token or IP check if exposed to web
        // For this demo, we assume it's called internally or via authenticated session
        if (! is_cli() && ! session()->get('isLoggedIn')) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $pageModel = new CatalogPageModel();

        // Find pages that are 'approved'.
        // Important: We must ensure the CATALOG itself is 'completed' (which means paid in our new logic).
        // Otherwise, user could approve pages but not pay, and we'd render them for free.

        // We join with catalogs table to check status
        $pages = $pageModel->select('catalog_pages.*')
                           ->join('catalogs', 'catalogs.id = catalog_pages.catalog_id')
                           ->where('catalog_pages.status', 'approved')
                           ->where('catalogs.status', 'completed') // Payment confirmed
                           ->orderBy('catalog_pages.created_at', 'ASC')
                           ->findAll($limit);

        if (empty($pages)) {
            return $this->response->setJSON(['status' => 'empty', 'message' => 'No pending paid pages']);
        }

        $service = new ImageGeneratorService();
        $results = [];

        foreach ($pages as $page) {
            $success = $service->generatePage($page['id']);
            $results[] = [
                'page_id' => $page['id'],
                'success' => $success
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'processed' => $results]);
    }
}
