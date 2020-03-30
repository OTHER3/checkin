<?php
namespace Shop\Controller;

use Service\QrCodeService;

/**
 * 用于重定向到指定页面
 */
class IndexController extends AuthController 
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('nav_name', 'index');
        $this->assign('static', STATIC_PATH);
    }

    public function index()
    {
        $shop_id = session('shop_id');
        $ShopModel = D('Shop');
        $detail = $ShopModel->where(['id' => $shop_id])->find();

        $qrcodeData = (new QrCodeService())->getWxaQrcode($shop_id);
        foreach ($qrcodeData as $key => $value) {
            if (!empty($detail['qr_staff']) && !empty($detail['qr_guest'])) {
                break;
            }

            if ($value['identity'] == QrCodeService::TYPE_GUEST) {
                $detail['qr_guest'] = STATIC_PATH . $value['path'];
            }
            if ($value['identity'] == QrCodeService::TYPE_STAFF) {
                $detail['qr_staff'] = STATIC_PATH . $value['path'];
            }
        }

        $this->assign('detail', $detail);
        $this->display();
    }
}