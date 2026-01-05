<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CatalogModel;
use CodeIgniter\Files\File;

class CatalogController extends BaseController
{
    public function index()
    {
        $catalogModel = new CatalogModel();
        // Join with user to get owner name
        $catalogs = $catalogModel->select('catalogs.*, users.full_name as owner_name')
                                 ->join('users', 'users.id = catalogs.user_id')
                                 ->findAll();

        return view('admin/catalogs/index', ['catalogs' => $catalogs]);
    }

    public function download($id)
    {
        // Download all images as zip (Phase 9 feature mentioned in Phase 8 plan prompt "Download catalog files")
        // Implementation logic:
        // 1. Check admin access (filter handles it).
        // 2. Zip the folder `writable/uploads/{id}`.
        // 3. Serve zip.

        $path = WRITEPATH . 'uploads/' . $id;
        if (!is_dir($path)) {
            return redirect()->back()->with('error', 'Files not found');
        }

        $zipPath = WRITEPATH . 'uploads/' . $id . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($path) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        }

        return $this->response->download($zipPath, null)->setFileName('catalog_' . $id . '.zip');
    }
}
