<?php
/**
 * 员工体温记录相关接口
 */
namespace Weixin\Controller;

use Weixin\Service\TemperatureService;

class TemperatureController extends BaseController
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new TemperatureService();
    }

    /**
     * 员工体温记录表
     */
    public function lists()
    {
        try {
            $user_id = getUserId();
            $page = I('page', 1);
            $limit = I('limit', 5);

            $list = $this->service->lists($user_id, $page, $limit);
            foreach ($list as $k => $v) {
                $list[$k]['created_at'] = date('Y/m/d H:i', $v['created_at']);
            }
            success($list);
        } catch (\Exception $e) {
            error($e);
        }
    }

    /**
     * 记录员工体温
     */
    public function add()
    {
        try {
            $user_id = getUserId();
            $temp = I('temp');
            $result = $this->service->add($user_id, $temp);
            success($result);
        } catch (\Exception $e) {
            error($e);
        }
    }
}