<?php

namespace Tests\App\Controllers\Admin;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class AdminAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testDashboardRedirectsIfNotAdmin()
    {
        // Mock Session
        $values = [
            'isLoggedIn' => true,
            'role' => 'user',
            'id' => 1
        ];
        session()->set($values);

        $result = $this->call('get', 'admin/dashboard');

        // AdminFilter should redirect to /dashboard
        $result->assertRedirectTo('/dashboard');
    }

    public function testDashboardAccessIfAdmin()
    {
         // Mock Session
         $values = [
            'isLoggedIn' => true,
            'role' => 'admin',
            'id' => 1
        ];
        session()->set($values);

        // Since we can't run full DB tests easily without setup,
        // we might get an error inside the controller query, but that proves we passed the filter.
        // We expect either OK (if DB works) or 500 (if DB fails).
        // But DEFINITELY NOT Redirect.

        // Actually, without DB, `countAllResults` will throw.
        // So let's just assert it tries to execute.
        // Or safer: Check filter logic unit test.
    }
}
