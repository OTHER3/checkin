<?php

namespace Weixin\Service;

use Service\VisitLogService;
use Weixin\Model\UserModel;

class LoginService
{
    protected $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    /**
     * 登陆授权
     * @param $code
     * @return array
     * @throws \Exception
     */
    public function getSession($code)
    {
        //step1 组装微信授权url
        $appId = C('appId');
        $secret = C('secret');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$secret}&js_code={$code}" .
            "&grant_type=authorization_code";
        try {
            //step2 请求微信授权获取openid
            $result = http($url, '');
            $result = json_decode($result, true);
            if (empty($result['openid'])) {
                throw new \Exception('openid获取失败');
            }
            $open_id = $result['openid'];
            //step3 检查用户是否已存入数据库中，无则新增用户数据
            $one = $this->model->where(['open_id' => $open_id])->find();
            if (empty($one)) {
                $data = [
                    'type' => UserModel::TYPE_VISITOR, //默认为访客
                    'shop_id' => 0,//默认无绑定
                    'open_id' => $open_id,
                    'name' => '',
                    'age' => 0,
                    'sex' => 1,
                    'mobile' => '',
                    'created_at' => time(),
                    'updated_at' => time(),
                ];

                $user_id = $this->model->add($data);
                $data['id'] = $user_id;
            } else {
                $data = $one;
            }

            $data['visit_jump'] = true;//控制小程序访客默认是否跳转到门店为1的问卷

            //step4 返回用户相关信息
            return $data;
        } catch (\Exception $e) {
            throw new \Exception('请求失败');
        }
    }
}