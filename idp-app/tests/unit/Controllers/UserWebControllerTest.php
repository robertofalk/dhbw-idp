<?php

namespace Tests\Unit\Controllers;

use App\Controllers\UserWebController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class UserWebControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testIndexReturnsWebUserView()
    {
        $result = $this->get('users-web');
        
        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('web_user', $result->getBody());
    }
} 