<?php
/**
 * 微信登陆授权
 */
namespace Weixin\Controller;

use Think\Controller;
use Weixin\Service\LoginService;

class LoginController extends Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new LoginService();
    }

    public function getSession() {
        try {
            $code = I('code');
            $result = $this->service->getSession($code);
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }
}