<?php
namespace Service;

class ShopService
{
    //账号状态: 1-待审核, 2-正常, 3-禁用
    const STATUS_WAIT_AUDIT = 1;
    const STATUS_NORMAL = 2;
    const STATUS_DISABLE = 3;

    const STATUS_MAP = [
        self::STATUS_WAIT_AUDIT => '待审核',
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    public function getOne(int $shop_id)
    {
        return D('Shop')->where(['id' => $shop_id])->find();
    }

    public function getShopName($shop_id)
    {
        $name = '';
        $shop = M('shop')->find($shop_id);
        if (!empty($shop)) {
            $name = $shop['shop_name'];
        }
        return $name;
    }
}