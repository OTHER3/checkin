<?php

namespace Weixin\Controller;

use Service\ShopService;
use Think\Controller;

class ShopController extends Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ShopService();
    }

    public function getShopName()
    {
        try {
            $shop_id = I('shop_id');
            $result = $this->service->getShopName($shop_id);
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }
}