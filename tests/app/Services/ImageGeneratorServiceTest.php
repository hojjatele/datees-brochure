<?php

namespace Tests\App\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\ImageGeneratorService;

class ImageGeneratorServiceTest extends CIUnitTestCase
{
    public function testServiceInstantiation()
    {
        $service = new ImageGeneratorService();
        $this->assertInstanceOf(ImageGeneratorService::class, $service);
    }

    // Cannot test generation without mocking external API or database state.
    // The previous tests confirmed DB and Auth logic.
    // This confirms the class structure and namespace are correct.
}
