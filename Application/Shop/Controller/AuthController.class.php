<?php
namespace Shop\Controller;

use Think\Controller;

class AuthController extends Controller
{
    /**
     * 登录状态验证
     *
     * @return void
     */
    public function _initialize()
    {
        $shop_id = session('shop_id');
        $shop_account = session('shop_account');

        if(empty($shop_id) || empty($shop_account)){
            $this->redirect('Login/index');
        }

        $this->assign('shop_id', $shop_id);
        $this->assign('shop_account', $shop_account);

        $action = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        $allow_action = [
            'shop/index/index',
            'shop/staff/lists',
            'shop/staff/edit',
            'shop/staff/savevisitlog',
            'shop/staff/temperature',
            'shop/staff/switch',
            'shop/guest/lists',
            'shop/guest/edit',
            'shop/guest/savevisitlog',
        ];

        //权限控制
        if(!in_array($action, $allow_action)){
            if (IS_AJAX) {
                $return = array(
                    'status' => 0,
                    'message' => '无权限操作！',
                );
                $this->ajaxReturn($return);
            } else {
                $this->error('无权限操作！', U('Index/index'));
            }
        }
    }
}