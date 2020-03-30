<?php
namespace Shop\Controller;

use Service\UserService;
use Service\VisitLogService;
use Think\Controller;

class GuestController extends AuthController
{
    private $VisitLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->assign('nav_name', 'guest');
        $this->assign('static', STATIC_PATH);
        $this->VisitLogModel = D('VisitLog');
    }

    public function lists()
    {
        $where = array();

        $where['v.shop_id'] = session('shop_id');
        $where['u.type'] = UserService::TYPE_GUEST;

        if(I('name') != ''){
            $name = I('name');
            $where['v.name'] = ['like', "%{$name}%"];
            $this->assign('name', I('name'));
        }
        if(I('mobile') != ''){
            $mobile = I('mobile');
            $where['v.mobile'] = ['like', "%{$mobile}%"];
            $this->assign('mobile', I('mobile'));
        }
        if(I('id_card') != ''){
            $id_card = I('id_card');
            $where['v.id_card'] = ['like', "%{$id_card}%"];
            $this->assign('id_card', I('id_card'));
        }
        if(I('shop_name') != ''){
            $shop_name = I('shop_name');
            $where['s.shop_name'] = ['like', "%{$shop_name}%"];
            $this->assign('shop_name', I('shop_name'));
        }

        $order = 'id desc';
        $page_size = 15;
        $count = $this->VisitLogModel->table('visit_log as v')
            ->join('shop as s on v.shop_id = s.id', 'left')
            ->join('user as u on v.user_id = u.id', 'left')
            ->where($where)
            ->count();
        $page = new \Think\Page($count, $page_size);
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $limit = $page->firstRow.','.$page->listRows;

        $list = $this->VisitLogModel->table('visit_log as v')
            ->join('shop as s on v.shop_id = s.id', 'left')
            ->join('user as u on v.user_id = u.id', 'left')
            ->where($where)
            ->field('v.*,s.shop_name,u.type as user_type')
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
        $where['v.id'] = I('id', 0);

        $detail = $this->VisitLogModel->table('visit_log as v')
            ->join('shop as s on v.shop_id = s.id', 'left')
            ->join('user as u on v.user_id = u.id', 'left')
            ->where($where)
            ->field('v.*,s.shop_name,u.type as user_type')
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
}