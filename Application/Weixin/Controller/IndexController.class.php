<?php
namespace Weixin\Controller;

use Service\ShopService;
use Service\VisitLogService;
use Think\Controller;

/**
 * 用于重定向到指定页面
 */
class IndexController extends Controller 
{
    public function index()
    {
        $this->ajaxReturn(['message' => 'success']);
    }
}