<?php
namespace Admin\Controller;

use Think\Controller;

/**
 * 用于重定向到指定页面
 */
class IndexController extends AuthController 
{
    public function index()
    {
        $this->redirect('Admin/User/lists');
    }
}