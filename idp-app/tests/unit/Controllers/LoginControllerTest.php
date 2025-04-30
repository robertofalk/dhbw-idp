<?php

namespace Tests\Unit\Controllers;

use App\Controllers\LoginController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class LoginControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testIndexReturnsLoginView()
    {
        $result = $this->get('/');
        
        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('index', $result->getBody());
    }
} 