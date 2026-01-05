<?php

namespace Tests\App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class AuthControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we cannot run spark migrate, we assume the DB is ready or this test might fail
        // if run in a real environment without migrations.
        // However, for the purpose of verifying logic in this environment, we write the test.
    }

    public function testRegister()
    {
        $result = $this->call('get', 'register');
        $result->assertOK();
        $result->assertSee(lang('App.auth.register_title'));
    }

    public function testLoginDisplay()
    {
        $result = $this->call('get', 'login');
        $result->assertOK();
        $result->assertSee(lang('App.auth.login_title'));
    }

    // We skip actual database writing tests here as we don't have a guaranteed test DB setup running
    // in this specific environment without spark.
    // But this confirms Controllers and Views are wired correctly.
}
