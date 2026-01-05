<?php

namespace Tests\App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class FeedbackTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testReviewPageProtected()
    {
        $result = $this->call('get', 'catalog/review/1');
        // Should redirect to login if not authenticated
        $result->assertRedirectTo('/login');
    }

    public function testFeedbackEndpointProtected()
    {
        $result = $this->call('post', 'catalog/feedback', ['page_id' => 1, 'user_feedback' => 'test']);
        $result->assertStatus(401); // Unauthorized from Controller
    }
}
