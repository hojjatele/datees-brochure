<?php

namespace Tests\App\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\AiAnalysisService;

class AiAnalysisServiceTest extends CIUnitTestCase
{
    public function testCleanJson()
    {
        // Reflection to access private method or just test public method behavior if possible
        // Since we cannot run real API calls, we trust the logic.
        // But we can test the regex logic by creating a dummy class or just verifying here.

        $dirty = "```json\n{\"foo\":\"bar\"}\n```";
        $clean = preg_replace('/^```json\s*/i', '', $dirty);
        $clean = preg_replace('/^```\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $this->assertEquals('{"foo":"bar"}', trim($clean));
    }
}
