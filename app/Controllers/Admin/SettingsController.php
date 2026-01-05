<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    private $allowedKeys = [
        'AI_ANALYSIS_MODEL',
        'AI_IMAGE_MODEL',
        'PRICE_PER_PAGE',
        'DATEES_PROJECT_ID', // Useful to edit
        'IMAGE_QUALITY'
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->allowedKeys as $key) {
            $settings[$key] = getenv($key);
        }

        return view('admin/settings', ['settings' => $settings]);
    }

    public function save()
    {
        $postData = $this->request->getPost();

        // Read .env file
        $envPath = ROOTPATH . '.env'; // Assuming it's in root
        if (!file_exists($envPath)) {
             // Try to copy from env
             if (file_exists(ROOTPATH . 'env')) {
                 copy(ROOTPATH . 'env', $envPath);
             } else {
                 // Create empty if not exists
                 file_put_contents($envPath, "");
             }
        }

        $envContent = file_get_contents($envPath);
        $lines = explode("\n", $envContent);
        $newLines = [];
        $keysFound = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                $newLines[] = $line;
                continue;
            }

            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);

            if (in_array($key, $this->allowedKeys) && isset($postData[$key])) {
                $newLines[] = $key . '=' . $postData[$key];
                $keysFound[] = $key;
            } else {
                $newLines[] = $line;
            }
        }

        // Add missing keys
        foreach ($this->allowedKeys as $key) {
            if (!in_array($key, $keysFound) && isset($postData[$key])) {
                $newLines[] = $key . '=' . $postData[$key];
            }
        }

        file_put_contents($envPath, implode("\n", $newLines));

        // We might need to reload environment, but in PHP, usually next request picks it up.
        // However, CI4 loads env at bootstrap.

        return redirect()->to('/admin/settings')->with('success', 'Settings updated successfully');
    }
}
