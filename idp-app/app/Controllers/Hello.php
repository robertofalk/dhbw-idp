<?php

namespace App\Controllers;

class Hello extends BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'message' => 'Hello from CodeIgniter!',
            'status' => 'success'
        ]);
    }

    public function web()
    {
        return view('web_hello');
    }
}
