<?php
/**
 * @Author chbin
 * @Email chenhanbin@jperation.com
 * @Time  2020/3/19 20:55
 * @Hotel
 * @Pms
 * @Version
 * parameter
 */
namespace Service;

class QrCodeService
{
    //类型 1-访客 2-员工
    const TYPE_GUEST = 1;
    const TYPE_STAFF = 2;

    /**
     * 获取小程序二维码, 不存在先生成
     *
     * @param integer $shop_id
     * @param integer $ques_id
     * @return array
     */
    public function getWxaQrcode(int $shop_id, int $ques_id = 0)
    {
        $QrCodeModel = D('QrCode');

        //已生成直接返回
        $qrcodeData = $QrCodeModel->where(['shop_id' => $shop_id])->limit(2)->select();
        if (!empty($qrcodeData)) {
            return $qrcodeData;
        }

        //生成小程序路径
        $page = "pages/scan/scan";
        $type = [self::TYPE_GUEST, self::TYPE_STAFF];

        if (empty($shop_id)) {
            throw new \Exception("门店id不能为空");
        }

        //TODO 门店是哪个表？哪个问卷表？

        $access_token = $this->getAccessToken();
        if (empty($access_token)) {
            throw new \Exception("ACCESS TOKEN为空！");
        }

        $return = [];
        foreach ($type as $k => $v){
            // $url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$access_token;
            // $data = '{"path": "pages/index/index?key="'.$storedId.'"||"'.$questionId.'"||"'.$v.', "width": 430}';
            //拼接页面参数
            $data = $shop_id . "#" . $ques_id . "#" . $v;
            $result = $this->mpcode($page , $data, $access_token);
            if ($result){
                $name = $v . md5(time()) . time();
                $path = "Public/Uploads/qrCode/";
                if(!is_dir($path)){
                    if (!mkdir($path,0777,true)){
                        throw new \Exception("文件夹生成失败");
                    }
                }
                if (!file_put_contents($path . $name . '.jpg', $result)){
                    throw new \Exception("生成小程序二维码失败");
                }
                $data = [
                    'shop_id' => $shop_id,
                    'ques_id' => $ques_id,
                    'identity' => $v,
                    'path' => '/Uploads/qrCode/'.$name.'.jpg',
                    'created_at' => time(),
                    'updated_at' => time(),
                ];

                $data['id'] = $QrCodeModel->add($data);
                if (!$data['id']){
                    throw new \Exception("生成二维码失败！请重试！");
                }

                $return[] = $data;
            }
        }

        return $return;
    }

    public function getAccessToken()
    {
        $appId = C('appId');
        $secret = C('secret');
        //TODO 可以通过数据库查询appid，secret，access_token（失效可以重新获取）
        $url_access_token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appId.'&secret='.$secret;
        $json_access_token = $this->curl_get($url_access_token);
        $arr_access_token = json_decode($json_access_token,true);
        return $arr_access_token['access_token'];
    }

    /**
     *重写数量不限制的码getWXACodeUnlimit
     *45009    调用分钟频率受限(目前5000次/分钟，会调整)，如需大量小程序码，建议预生成。
     *41030    所传page页面不存在，或者小程序没有发布
     */
    public function mpcode($page,$cardid,$access_token)
    {
        //参数
//        $postdata['scene']="nidaodaodao";
        $postdata['scene']=$cardid;
        // 宽度
        $postdata['width']=430;
        // 页面
        $postdata['page']=$page;
//        $postdata['page']="pages/postcard/postcard";
        // 线条颜色
        $postdata['auto_color']=false;
        //auto_color 为 false 时生效
        $postdata['line_color']=['r'=>'0','g'=>'0','b'=>'0'];
        // 是否有底色为true时是透明的
        $postdata['is_hyaline']=true;
        $post_data = json_encode($postdata);
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;

        return $this->curl_get($url,$post_data);
    }

    /**
     * 发起请求
     * @param  string $url  请求地址
     * @param  string $data 请求数据包
     * @return   string      请求返回数据
     */
    public function curl_get($url, $data = '')
    {
        $ch = curl_init();
        $header=array('Accept-Language:zh-CN','x-appkey:114816004000028','x-apsignature:933931F9124593865313864503D477035C0F6A0C551804320036A2A1C5DF38297C9A4D30BB1714EC53214BD92112FB31B4A6FAB466EEF245710CC83D840D410A7592D262B09D0A5D0FE3A2295A81F32D4C75EBD65FA846004A42248B096EDE2FEE84EDEBEBEC321C237D99483AB51235FCB900AD501C07A9CAD2F415C36DED82','x-apversion:1.0','Content-Type:application/x-www-form-urlencoded','Accept-Charset: utf-8','Accept:application/json','X-APFormat:json');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        return $tmpInfo;
    }
}