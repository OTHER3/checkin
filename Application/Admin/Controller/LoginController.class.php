<?php
namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller
{
    /**
     * 登录状态验证
     *
     * @return void
     */
    public function index()
    {
        $admin_id = session('admin_id');
        if(!empty($admin_id)){
            $this->redirect('Admin/User/lists');
        }

        $this->display('login');
    }

    /**
     * 验证登录
     *
     * @return void
     */
    public function validation()
    {
        $return = array(
            'status' => 0,
            'message' => '',
        );

        if(!IS_POST){
            $return['message'] = '提交方式不正确！';
            $this->ajaxReturn($return);
        }

        $password = I('password');
        $verifyCode = I('verifyCode');

        //验证码校验
        $Verify = new \Think\Verify();
        $verifyResult = $Verify->check($verifyCode);
        if(!$verifyResult){
            $return['message'] = '验证码不正确！';
            $this->ajaxReturn($return);
        }

        $where = array();
        $where['account'] = I('account');
        if(empty($where['account'])){
            $return['message'] = "请输入帐号！";
            $this->ajaxReturn($return);
        }
        if(empty($password)){
            $return['message'] = "请输入密码！";
            $this->ajaxReturn($return);
        }

        $AccountModel = D('account');
        $detail = $AccountModel->where($where)->find();

        if(empty($detail)){
            $return['status'] = 0;
            $return['message'] = '用户不存在！';
            $this->ajaxReturn($return);
        }
        if(md5($password) !== $detail['password']){
            $return['status'] = 0;
            $return['message'] = '密码不正确！';
            $this->ajaxReturn($return);
        }

        session('admin_id', $detail['id']);
        session('admin_type', $detail['type']);
        session('admin_name', $detail['account']);

        $return['status'] = 1;
        $return['message'] = '登录成功！';
        $this->ajaxReturn($return);
    }

    public function logout(){
        session(null);
        $return['status'] = 1;
        $return['message'] = '退出成功';
        $this->ajaxReturn($return);
    }

    public function register() {
        $this->display();
    }

    public function doRegister() {
        $return = array(
            'status' => 0,
            'message' => '',
        );

        $return['message'] = '注册功能未开放！';
        $this->ajaxReturn($return);

        if(!IS_POST){
            $return['message'] = '提交方式不正确！';
            $this->ajaxReturn($return);
        }

        $data = [
            'account' => I('account', '', 'trim'),
            'shop_name' => I('shop_name', '', 'trim'),
            'password' => I('password', '', 'trim'),
            'password2' => I('password2', '', 'trim'),
            'type' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        if(empty($data['account'])){
            $return['message'] = "请输入帐号！";
            $this->ajaxReturn($return);
        }
        if(empty($data['shop_name'])){
            $return['message'] = "请输入门店名称！";
            $this->ajaxReturn($return);
        }
        if(empty($data['password'])){
            $return['message'] = "请输入密码！";
            $this->ajaxReturn($return);
        }
        if(empty($data['password2'])){
            $return['message'] = "请输入确认密码！";
            $this->ajaxReturn($return);
        }
        if($data['password2'] !== $data['password2']){
            $return['message'] = "两次输入的密码不一致！";
            $this->ajaxReturn($return);
        }

        $AccountModel = D('account');

        $where = array();
        $where['account'] = $data['account'];
        $detail = $AccountModel->where($where)->find();
        if(!empty($detail)){
            $return['status'] = 0;
            $return['message'] = "用户：{$data['account']}已存在！";
            $this->ajaxReturn($return);
        }

        unset($data['password2']);
        $data['password'] = md5($data['password']);

        $id = $AccountModel->add($data);
        if(!$id){
            $return['status'] = 0;
            $return['message'] = '用户注册失败！请重试！';
            $this->ajaxReturn($return);
        }

        session('admin_id', $id);
        session('admin_type', $data['type']);
        session('admin_name', $data['account']);

        $return['status'] = 1;
        $return['message'] = '注册成功！';
        $this->ajaxReturn($return);
    }
    
    /**
     * 生成验证码
     * @return void
     */
    public function verifyCode() {
        $config = array(
            'imageW' => 140,
            'imageH' => 35,
            'fontSize' => 18,       // 验证码字体大小
            'length' => 4,          // 验证码位数
            'useNoise' => false,    // 关闭验证码杂点
            'useCurve' => false,
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
}