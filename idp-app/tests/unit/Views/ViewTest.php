<?php

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\View;
use Config\Services;
use Config\View as ViewConfig;

class ViewTest extends CIUnitTestCase
{
    protected $view;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = new ViewConfig();
        $this->config->saveData = true;
        
        $this->view = new View($this->config, APPPATH . 'Views/');
        $this->view->setData(['showLogout' => false]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->view = null;
    }

    public function testLoginViewContainsRequiredElements()
    {
        $this->view->setData(['showLogout' => false]);
        $output = $this->view->render('index');

        // Test for required HTML elements
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('<title>Login</title>', $output);
        $this->assertStringContainsString('<link rel="stylesheet" href="/css/style.css">', $output);
        
        // Test for form elements
        $this->assertStringContainsString('<form id="login-form">', $output);
        $this->assertStringContainsString('<input type="text" id="username"', $output);
        $this->assertStringContainsString('<input type="password" id="password"', $output);
        $this->assertStringContainsString('<button type="submit">Login</button>', $output);
        
        // Test for JavaScript functionality
        $this->assertStringContainsString('handleLogin(event)', $output);
        $this->assertStringContainsString('fetch(\'/auth/login\'', $output);
        $this->assertStringContainsString('localStorage.setItem(\'token\'', $output);
    }

    public function testWebUserViewContainsRequiredElements()
    {
        $this->view->setData(['showLogout' => true]);
        $output = $this->view->render('web_user');

        // Test for required HTML elements
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('<title>User Management</title>', $output);
        $this->assertStringContainsString('<link rel="stylesheet" href="/css/style.css">', $output);
        
        // Test for user management form
        $this->assertStringContainsString('<form id="user-form">', $output);
        $this->assertStringContainsString('<input type="text" id="name"', $output);
        $this->assertStringContainsString('<select id="role">', $output);
        $this->assertStringContainsString('<input type="password" id="password">', $output);
        $this->assertStringContainsString('<button id="submit-button"', $output);
        
        // Test for user list table
        $this->assertStringContainsString('<table border="1"', $output);
        $this->assertStringContainsString('<thead>', $output);
        $this->assertStringContainsString('<tbody id="user-list">', $output);
        
        // Test for chat functionality
        $this->assertStringContainsString('id="chat-toggle"', $output);
        $this->assertStringContainsString('id="chat-box"', $output);
        $this->assertStringContainsString('id="chat-messages"', $output);
        $this->assertStringContainsString('id="chat-form"', $output);
        
        // Test for JavaScript functionality
        $this->assertStringContainsString('fetchUsers()', $output);
        $this->assertStringContainsString('handleSubmit(event)', $output);
        $this->assertStringContainsString('handleChat(event)', $output);
        $this->assertStringContainsString('localStorage.getItem(\'token\')', $output);
    }

    public function testLoginViewHeaderPartial()
    {
        $this->view->setData(['showLogout' => false]);
        $output = $this->view->render('partials/header');

        $this->assertStringContainsString('<header>', $output);
        $this->assertStringNotContainsString('logout', $output);
    }

    public function testWebUserViewHeaderPartial()
    {
        $this->view->setData(['showLogout' => true]);
        $output = $this->view->render('partials/header');

        $this->assertStringContainsString('<header>', $output);
        $this->assertStringContainsString('logout', $output);
    }
} 