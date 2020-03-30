<?php
namespace Admin\Controller;

use Service\ShopService;

class ShopController extends AuthController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('nav_name', 'shop');
        $this->assign('static', STATIC_PATH);
        $this->assign('status_map', ShopService::STATUS_MAP);
    }

    public function lists()
    {
        $ShopModel = D('Shop');
        $where = array();
        if(I('shop_name') != ''){
            $shop_name = I('shop_name');
            $where['shop_name'] = ['like', "%{$shop_name}%"];
            $this->assign('shop_name', I('shop_name'));
        }
        if(I('account') != ''){
            $account = I('account');
            $where['account'] = ['like', "%{$account}%"];
            $this->assign('account', I('account'));
        }
        if(I('mobile') != ''){
            $mobile = I('mobile');
            $where['mobile'] = ['like', "%{$mobile}%"];
            $this->assign('mobile', I('mobile'));
        }

        $order = 'id asc';
        $page_size = 15;
        $count = $ShopModel->where($where)->count();
        $page = new \Think\Page($count, $page_size);
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $limit = $page->firstRow.','.$page->listRows;

        $list = $ShopModel->field('*')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();
        $this->assign('list', $list);
        $this->assign('page', bootstrap_page_style($page->show()));
        $this->display();
    }

    public function add()
    {
        $this->display();
    }

    public function edit()
    {
        $id = I('id', 0);
        if(!$id || !is_numeric($id)){
            $this->error('提交的数据不正确！');
        }
        
        $ShopModel = D('Shop');
        $detail = $ShopModel->where(array('id'=>$id))->find();
        if(!$detail || empty($detail)){
            $this->error('数据不存在！请刷新后重试！');
        }
        $this->assign('detail', $detail);
        $this->display();
    }

    /**
     * 保存用户信息
     *
     * @return void
     */
    public function save()
    {
        $return = array(
            'status' => 0,
            'message' => '',
        );

        if(!IS_POST){
            $return['message'] = '提交方式不正确！';
            $this->ajaxReturn($return);
        }

        $data = array();
        $data['id'] = I('id', 0, 'trim');
        $data['shop_name'] = I('shop_name', '', 'trim');
        $data['account'] = I('account', '', 'trim');
        $data['password'] = I('password', '', 'trim');
        $data['manager'] = I('manager', '', 'trim');
        $data['mobile'] = I('mobile', '', 'trim');
        $data['address'] = I('address', '', 'trim');
        $data['business_license'] = I('business_license', '', 'trim');
        $data['creator_id'] = session('admin_id');
        $data['created_at'] = time();
        $data['updated_at'] = time();
        
        if($data['shop_name'] == ''){
            $return['message'] = '请填写门店名称！';
            $this->ajaxReturn($return);
        }
        if($data['account'] == ''){
            $return['message'] = '请填写门店账号！';
            $this->ajaxReturn($return);
        }
        if(!$data['id'] && empty($data['password'])){
            $return['message'] = '请填写密码！';
            $this->ajaxReturn($return);
        }
        if (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        }

        if($data['manager'] == ''){
            $return['message'] = '请输入门店负责人！';
            $this->ajaxReturn($return);
        }
        if($data['mobile'] == ''){
            $return['message'] = '请输入联系电话！';
            $this->ajaxReturn($return);
        }
        if($data['address'] == ''){
            $return['message'] = '请输入门店地址！';
            $this->ajaxReturn($return);
        }
        if($data['business_license'] == ''){
            $return['message'] = '请输入营业执照图片！';
            $this->ajaxReturn($return);
        }

        //禁止重复名称
        $ShopModel = D('Shop');
        $where = array();
        $where['account'] = $data['account'];
        if($data['id'] && is_numeric($data['id'])){
            $where['id'] = array('neq', $data['id']);
        }
        $count = $ShopModel->where($where)->count();
        if($count > 0){
            $return['message'] = '该门店账号已存在！请修改！';
            $this->ajaxReturn($return);
        }

        if($data['id'] && is_numeric($data['id'])){
            //修改
            if (empty($data['password'])) {
                unset($data['password']);
            }

            unset($data['created_at']);
            $data['status'] = I('status', ShopService::STATUS_WAIT_AUDIT, 'trim');
            $result = $ShopModel->where("id={$data['id']}")->save($data);
            $return['message'] = '修改成功！';
        }else{
            //新增
            $data['status'] = ShopService::STATUS_NORMAL;
            $result = $ShopModel->add($data);
            $return['message'] = '添加成功！';
        }
        if(!$result){
            $return['message'] = '操作失败！请重试！';
            $this->ajaxReturn($return);
        }
        
        $return['status'] = 1;
        $this->ajaxReturn($return);
    }

    public function delete()
    {
        $return = array(
            'status' => 0,
            'message' => '',
        );

        $id = I('id', 0);
        if(!IS_POST){
            $return['message'] = '提交方式不正确！';
            $this->ajaxReturn($return);
        }
        if(!$id || !is_numeric($id)){
            $return['message'] = '提交的数据不正确！';
            $this->ajaxReturn($return);
        }
        
        $ShopModel = D('Shop');
        $detail = $ShopModel->where(array('id'=>$id))->find();
        if(!$detail || empty($detail)){
            $return['message'] = '删除失败！数据不存在！';
            $this->ajaxReturn($return);
        }
        $result = $ShopModel->where(array('id'=>$id))->delete();
        if(!$result){
            $return['message'] = '删除失败！请检查后重试！';
            $this->ajaxReturn($return);
        }

        $return['status'] = 1;
        $return['message'] = '删除成功！';
        $this->ajaxReturn($return);
    }
}