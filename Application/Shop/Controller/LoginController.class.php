<?php
namespace Shop\Controller;

use Service\ShopService;
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
        $shop_id = session('shop_id');
        if(!empty($shop_id)){
            $this->redirect('Index/index');
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

        $ShopModel = D('Shop');
        $detail = $ShopModel->where($where)->find();

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

        if ($detail['status'] == ShopService::STATUS_WAIT_AUDIT) {
            $return['status'] = 0;
            $return['message'] = '账号未审核通过!';
            $this->ajaxReturn($return);
        }
        if ($detail['status'] == ShopService::STATUS_DISABLE) {
            $return['status'] = 0;
            $return['message'] = '账号被停用!';
            $this->ajaxReturn($return);
        }
        if ($detail['status'] != ShopService::STATUS_NORMAL) {
            $return['status'] = 0;
            $return['message'] = '账号状态异常!';
            $this->ajaxReturn($return);
        }

        session('shop_id', $detail['id']);
        session('shop_account', $detail['account']);

        $return['status'] = 1;
        $return['message'] = '登录成功！';
        $this->ajaxReturn($return);
    }

    public function logout(){
        session('shop_id', null);
        session('shop_account', null);

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
        $data['creator_id'] = 0;
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

        $ShopModel = D('Shop');

        //禁止重复名称
        $where = array();
        $where['account'] = $data['account'];
        $count = $ShopModel->where($where)->count();
        if($count > 0){
            $return['message'] = '该门店账号已存在！请修改！';
            $this->ajaxReturn($return);
        }

        $result = $ShopModel->add($data);
        if(!$result){
            $return['message'] = '操作失败！请重试！';
            $this->ajaxReturn($return);
        }
        
        $return['message'] = '注册成功, 等待后台审核！';
        $return['status'] = 1;
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