<?php
namespace Admin\Controller;

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
        $admin_id = session('admin_id');
        $admin_type = session('admin_type');
        $admin_name = session('admin_name');

        if(empty($admin_id) || empty($admin_type) || empty($admin_name)){
            $this->redirect('Admin/Login/index');
        }

        $this->assign('admin_id', $admin_id);
        $this->assign('admin_type', $admin_type);
        $this->assign('admin_name', $admin_name);

        //MODULE_NAME 当前模块名  CONTROLLER_NAME 当前控制器名  ACTION_NAME 当前操作名  
        $action = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        $allow_action = [
            'admin/index/index',
            'admin/task/lists',
            'admin/shop/lists',
            'admin/shop/add',
            'admin/shop/edit',
            'admin/shop/save',
            'admin/user/lists',
        ];

        //权限控制：普通用户禁止操作
        if($admin_type == 1 && !in_array($action, $allow_action)){
            if (IS_AJAX) {
                $return = array(
                    'status' => 0,
                    'message' => '无权限操作！',
                );
                $this->ajaxReturn($return);
            } else {
                $this->error('无权限操作！', U('Admin/Index/index'));
            }
        }
    }
}