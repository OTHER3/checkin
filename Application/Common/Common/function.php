<?php

function p($data){
    //判断是否为命令行运行
    if(!empty($_SERVER['argv'])){
        //windows环境下：将数组或字符串转成gbk编码，解决命令行输出乱码问题
        if(strpos(strtolower(PHP_OS), 'win') !== false){
            //方法：var_export将数组变成php代码(字符串形式)，iconv转码，用eval执行php代码，得到新数组
            $data = eval('return '.iconv('utf-8', 'gbk//IGNORE', var_export($data, true).';'));
        }
        print_r($data);
        echo PHP_EOL;
    }else{
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
    return true;
}

/**
 * Thinkphp默认分页样式转Bootstrap分页样式
 * @param string $page_html tp默认输出的分页html代码
 * @return string 新的分页html代码
 */
function bootstrap_page_style($page_html){
    if ($page_html) {
        $page_show = str_replace('<div>','<nav><ul class="pagination" style="margin-top:-10px;">',$page_html);
        $page_show = str_replace('</div>','</ul></nav>',$page_show);
        $page_show = str_replace('<span class="current">','<li class="active"><a>',$page_show);
        $page_show = str_replace('</span>','</a></li>',$page_show);
        $page_show = str_replace(array('<a class="num"','<a class="prev"','<a class="next"','<a class="end"','<a class="first"'),'<li><a',$page_show);
        $page_show = str_replace('</a>','</a></li>',$page_show);
    }
    return $page_show;
}

/**
 * 输出json字符串
 *
 * @param [type] $data
 * @return void
 */
function ajax_return($data)
{
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode($data, 256);
    die;
}