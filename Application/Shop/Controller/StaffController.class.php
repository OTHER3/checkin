<?php
namespace Shop\Controller;

use Service\UserService;
use Service\VisitLogService;
use Think\Controller;

/**
 * 员工管理
 */
class StaffController extends AuthController
{
    private $VisitLogModel;
    private $UserModel;

    public function __construct()
    {
        parent::__construct();
        $this->assign('nav_name', 'staff');
        $this->assign('static', STATIC_PATH);
        $this->VisitLogModel = D('VisitLog');
        $this->UserModel = D('User');
    }

    public function lists()
    {
        $where = array();

        $where['u.shop_id'] = session('shop_id');
        $where['u.type'] = UserService::TYPE_STAFF;

        if(I('name') != ''){
            $name = I('name');
            $where['u.name'] = ['like', "%{$name}%"];
            $this->assign('name', I('name'));
        }
        if(I('mobile') != ''){
            $mobile = I('mobile');
            $where['u.mobile'] = ['like', "%{$mobile}%"];
            $this->assign('mobile', I('mobile'));
        }
        if(I('id_card') != ''){
            $id_card = I('id_card');
            $where['u.id_card'] = ['like', "%{$id_card}%"];
            $this->assign('id_card', I('id_card'));
        }
        if(I('shop_name') != ''){
            $shop_name = I('shop_name');
            $where['s.shop_name'] = ['like', "%{$shop_name}%"];
            $this->assign('shop_name', I('shop_name'));
        }

        $order = 'id desc';
        $page_size = 15;
        $count = $this->UserModel->table('user as u')
            ->join('shop as s on u.shop_id = s.id', 'left')
            ->where($where)
            ->count();
        $page = new \Think\Page($count, $page_size);
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $limit = $page->firstRow.','.$page->listRows;

        $list = $this->UserModel->table('user as u')
            ->join('shop as s on u.shop_id = s.id', 'left')
            ->where($where)
            ->field('u.*,s.shop_name')
            ->order($order)
            ->limit($limit)
            ->select();
        $this->assign('list', $list);
        $this->assign('page', bootstrap_page_style($page->show()));
        $this->display();
    }

    public function edit()
    {
        $where = [];
        $where['v.user_id'] = I('staff_id', 0);

        $detail = $this->VisitLogModel->table('visit_log as v')
            ->join('shop as s on v.shop_id = s.id', 'left')
            ->join('user as u on v.user_id = u.id', 'left')
            ->where($where)
            ->field('v.*,s.shop_name,u.type as user_type, u.status as user_status')
            ->find();

            if (!empty($detail['results'])) {
            $detail['results'] = json_decode($detail['results'], true);
        }

        $topic = (new VisitLogService())->getTopic();

        $this->assign('topic', $topic);
        $this->assign('detail', $detail);
        $this->display();
    }

    public function saveVisitLog()
    {
        $input = I('post.');
        $input['results'] = json_encode($input['results']);

        $result = (new VisitLogService())->updateVisitLog($input['visit_log_id'], $input);

        $return = array(
            'status' => 0,
            'message' => '',
        );

        if(!$result){
            $return['message'] = '更新失败!请重试';
            $this->ajaxReturn($return);
        }

        //更新用户信息
        $this->UserModel->where(['id' => $input['user_id']])->save($input);

        $return['status'] = 1;
        $return['message'] = '更新成功';
        $this->ajaxReturn($return);
    }

    public function delete()
    {
        $return = array(
            'status' => 0,
            'message' => '',
        );

        $id = I('id', 0);

        try {
            if(!IS_POST){
                throw new \Exception("提交方式不正确！");
            }
            if(!$id || !is_numeric($id)){
                throw new \Exception("提交的数据不正确！");
            }
            (new VisitLogService())->delete($id);
        } catch (\Throwable $th) {
            $return['message'] = $th->getMessage();
            $this->ajaxReturn($return);
        }

        $return['status'] = 1;
        $return['message'] = '删除成功！';
        $this->ajaxReturn($return);
    }

    /**
     * 员工体温
     */
    public function temperature()
    {
        $user_id = I('user_id', 0);
        $UserTemperatureModel = D('UserTemperature');
        $list = $UserTemperatureModel->where(['user_id' => $user_id])
            ->order('id desc')
            ->select();

        $this->assign('list', $list);
        $this->display();
    }

    //修改员工状态
    public function switch()
    {      
        $return = array(
            'status' => 0,
            'message' => '',
        );

        $user_id = I('user_id', 0);
        $status = I('status', 0);

        try {
            if(empty($user_id) || empty($status)){
                throw new \Exception("提交的数据不正确！");
            }

            $update_data = [
                'status' => $status,
                'shop_id' => 0,
                'type' => UserService::TYPE_GUEST,
            ];
            $result = $this->UserModel->where(['id' => $user_id])->save($update_data);
            if (!$result) {
                throw new \Exception("修改失败!");
            }
        } catch (\Throwable $th) {
            $return['message'] = $th->getMessage();
            $this->ajaxReturn($return);
        }

        $return['status'] = 1;
        $return['message'] = '修改成功!';
        $this->ajaxReturn($return);
    }
}