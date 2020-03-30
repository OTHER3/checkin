<?php

/**
 * 格式化成功返回数据
 * @param $data
 * @param string $message
 */
function success($data, $message = 'SUCCESS') {
    $result = [
        'code' => 0,
        'message' => $message,
        'data' => $data,
    ];
    ajax_return($result);
}

/**
 * 格式化失败返回数据
 * @param Exception $e
 */
function error(\Exception $e) {
    $result = [
        'code' => $e->getCode() ? $e->getCode() : -1,
        'message' => $e->getMessage(),
        'data' => []
    ];
    ajax_return($result);
}

/**
 * 发送HTTP请求方法
 * @param $url
 * @param $params
 * @param string $method
 * @param array $header
 * @param bool $multi
 * @return bool|string
 * @throws Exception
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false){
    $opts = array(
        CURLOPT_TIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => $header
    );

    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }

    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return $data;
}

/**
 * 生成token
 * @param $user_id
 * @return string
 */
function makeToken($user_id)
{
    $date = date('Y-m-d', time());
    return sha1(md5($user_id . ':' . $date));
}

/**
 * 校验token
 * @param $user_id
 * @param $token
 * @return bool
 */
function checkToken($user_id, $token) {
    return makeToken($user_id) == $token;
}

/**
 * 获取用户id
 * @return mixed
 */
function getUserId() {
    $clientInfo = $_SERVER['HTTP_CLIENTINFO'];
    $clientInfo = json_decode($clientInfo, true);
    if (empty($clientInfo['user_id'])) {
        error(new \Exception('请求失败'));
    }
    return $clientInfo['user_id'];
}

function post() {
    return json_decode(file_get_contents('php://input'), true);
}