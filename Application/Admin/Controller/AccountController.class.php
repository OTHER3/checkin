<?php
namespace Admin\Controller;

use Think\Controller;

class AccountController extends AuthController
{
    protected $user_type_list = [
        1 => '普通账号',
        2 => '管理员',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->assign('nav_name', 'account');
        $this->assign('static', STATIC_PATH);
        $this->assign('user_type_list', $this->user_type_list);
    }

    public function lists()
    {
        $AccountModel = D('Account');
        $where = array();
        if(I('account') != ''){
            $account = I('account');
            $where['account'] = ['like', "%{$account}%"];
            $this->assign('account', I('account'));
        }

        $order = 'id asc';
        $page_size = 15;
        $count = $AccountModel->where($where)->count();
        $page = new \Think\Page($count, $page_size);
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $limit = $page->firstRow.','.$page->listRows;

        $list = $AccountModel->field('*')
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
        
        $AccountModel = D('Account');
        $detail = $AccountModel->where(array('id'=>$id))->find();
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
        $data['account'] = I('account', '', 'trim');
        $data['password'] = I('password', '', 'trim');
        $data['type'] = I('user_type', '', 'trim');
        $data['created_at'] = time();
        $data['updated_at'] = time();
        
        if($data['account'] == ''){
            $return['message'] = '请填写用户名！';
            $this->ajaxReturn($return);
        }
        if(!$data['id'] && empty($data['password'])){
            $return['message'] = '请填写用户密码！';
            $this->ajaxReturn($return);
        }
        if($data['type'] != 1 && $data['type'] != 2){
            $return['message'] = '用户类型不正确！';
            $this->ajaxReturn($return);
        }
        if (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        }

        //禁止重复名称
        $AccountModel = D('Account');
        $where = array();
        $where['account'] = $data['account'];
        if($data['id'] && is_numeric($data['id'])){
            $where['id'] = array('neq', $data['id']);
        }
        $count = $AccountModel->where($where)->count();
        if($count > 0){
            $return['message'] = '该用户名已存在！请修改！';
            $this->ajaxReturn($return);
        }

        if($data['id'] && is_numeric($data['id'])){
            if (empty($data['password'])) {
                unset($data['password']);
            }

            //修改
            unset($data['created_at']);
            $result = $AccountModel->where("id={$data['id']}")->save($data);
            $return['message'] = '修改成功！';
        }else{
            //新增
            $result = $AccountModel->add($data);
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
        
        $AccountModel = D('Account');
        $detail = $AccountModel->where(array('id'=>$id))->find();
        if(!$detail || empty($detail)){
            $return['message'] = '删除失败！数据不存在！';
            $this->ajaxReturn($return);
        }
        $result = $AccountModel->where(array('id'=>$id))->delete();
        if(!$result){
            $return['message'] = '删除失败！请检查后重试！';
            $this->ajaxReturn($return);
        }

        $return['status'] = 1;
        $return['message'] = '删除成功！';
        $this->ajaxReturn($return);
    }
}