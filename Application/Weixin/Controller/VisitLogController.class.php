<?php
/**
 * 出入登记
 */
namespace Weixin\Controller;

use Service\VisitLogService;
use Think\Controller;

class VisitLogController extends Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new VisitLogService();
    }

    /**
     * 获取用户最后一次登记
     */
    public function userTopic()
    {
        try {
            $user_id = getUserId();
            $shop_id = I('shop_id');
            $ques_id = I('ques_id');
            $result = $this->service->userTopic($user_id, $shop_id, $ques_id);
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }

    /**
     * 获取问卷题目
     * @return array
     */
    public function getTopic()
    {
        try {
            $ques_id = I('ques_id');
            $result = $this->service->getTopic($ques_id);
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }

    /**
     * 添加纪录
     */
    public function addTopic()
    {
        try {
            $post = post();
            $shop_id = $post['shop_id'];
            $ques_id = $post['ques_id'];
            $form_data = $post['form_data'];
            $result = $this->service->dealAddParam($shop_id, $ques_id, $form_data);
            success([$form_data, $result]);
        } catch (\Exception $e) {
            error($e);
        }
    }
}