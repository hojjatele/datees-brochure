<?php

namespace Tests\App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class ExportControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testDownloadProtected()
    {
        $result = $this->call('get', 'catalog/download/1');
        $result->assertRedirectTo('/login');
    }

    // We cannot easily test PDF generation output without mocking TCPDF or having a rendered catalog in DB
}
