<?php

namespace Weixin\Service;


use Weixin\Model\TemperatureModel;
use Weixin\Model\UserModel;

class TemperatureService
{
    protected $model;

    public function __construct()
    {
        $this->model = new TemperatureModel();
    }

    public function lists($user_id, $page, $limit)
    {
        return $this->model->where(['user_id' => $user_id])->order('id desc')->page($page)->limit($limit)->select();
    }

    public function add($user_id, $temp)
    {
        //检查用户是否是员工身份
        $this->checkUserIdType($user_id, 2);
        $data = [
            'user_id' => $user_id,
            'temperature' => $temp,
            'created_at' => time()
        ];
        return $this->model->add($data);
    }

    /**
     * 检查用户是否是特定类型用户
     * @param $user_id
     * @param $type
     * @throws \Exception
     */
    public function checkUserIdType($user_id, $type)
    {
        $userModel = new UserModel();
        $userInfo = $userModel->find($user_id);
        if (empty($userInfo) || $userInfo['type'] != $type) {
            throw new \Exception('不可添加');
        }
    }
}