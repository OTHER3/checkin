<?php

namespace Weixin\Controller;

use Think\Controller;
use Weixin\Util\Code;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();
//        $this->checkToken();
    }

    private function checkToken()
    {
        try {
            $clientInfo = $_SERVER['HTTP_CLIENTINFO'];
            $clientInfo = json_decode($clientInfo, true);
            if (!isset($clientInfo['token']) || !isset($clientInfo['user_id'])) {
                throw new \Exception('参数错误', Code::RE_LOGIN);
            }

            $token   = $clientInfo['token'];
            $user_id = $clientInfo['user_id'];
            if (checkToken($user_id, $token)) {
                throw new \Exception('请求失败',Code::RE_LOGIN);
            }
        } catch (\Exception $e) {
            error($e);
        }
    }
}