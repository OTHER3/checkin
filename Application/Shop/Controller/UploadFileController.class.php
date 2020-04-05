<?php
namespace Shop\Controller;

use Service\UploadService;
use Think\Controller;

class UploadFileController extends Controller
{
    protected $UploadService;

    public function __construct()
    {
        $this->UploadService = new UploadService();
    }

    public function upload()
    {
        $return = [
            'status' => 0,
            'message' => '',
        ];
        
        $action = I('action');
        if(!$action || $action == ''){
            $return['message'] = '非法访问！';
            ajax_return($return);
        }
        if($action != 'upload' && $action != 'merge'){
            $return['message'] = '非法操作！';
            ajax_return($return);
        }

        //上传或者合并
        if($_POST['action'] == 'upload' ){
            $return = $this->UploadService->uploadFile();
        }
        if($_POST['action'] == 'merge' ){
            $return = $this->UploadService->mergeFile();
        }
        ajax_return($return);
    }
}