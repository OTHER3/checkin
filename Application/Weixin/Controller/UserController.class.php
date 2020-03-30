<?php

namespace Weixin\Controller;

use Service\UserService;
use Think\Controller;

class UserController extends Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UserService();
    }

    /**
     * 用户详情
     */
    public function detail()
    {
        try {
            $result = $this->service->detail();
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }

    /**
     * 用户绑定门店
     */
    public function bindShop()
    {
        try {
            $shop_id = I('shop_id', 0);
            if (!$shop_id) {
                success([]);
            }

            $result = $this->service->bindShop(getUserId(), $shop_id);
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }

    /**
     * 用户隐私协议
     * @return string
     */
    public function agreement()
    {
        return success($this->service->agreement());
    }
}