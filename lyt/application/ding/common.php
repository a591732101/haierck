<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Author: dd
// +----------------------------------------------------------------------
// 应用公共文件
//加载thinkphp 类 / 助手函数


/**
 * json封装
 * @param int $code 状态码
 * @param string $msg   说明
 * @param array $data   其他参数
 * @return Json
 */
function setjsons($msg='',$code=0,$data=[]){
    $arr = ['code'=>$code,'msg'=>$msg];
    if($data){
        foreach ($data as $k => $v){
            $arr[$k] = $v;
        }
    }
    return json($arr);
}

/**
 * 参数说明
 * @param string $code 状态码
 * @return string
 */
function msg($code){
    switch ($code){
        case 100:
            $msg = '操作成功';
            break;
        case 101:
            $msg = '操作失败';
            break;
        case 102:
            $msg = '请输入对应信息！';
            break;
        default:
            $msg = '请求成功';
    }
    return $msg;
}


