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
 * @return \think\response\Json
 */
function setjsons($msg='',$code=0,$data=[]){
    if($msg=='')$msg='请求成功';
    $arr = ['code'=>$code,'msg'=>$msg];
    if($data){
        foreach ($data as $k => $v){
            $arr[$k] = $v;
        }
    }
    return json($arr);
}

/**
 * 提示说明-封装
 * @param number $code 提示状态码
 * @return string 说明文案
 */
function msg($code = 0000){
    switch ($code){
        case 0000:
            $msg = '请求成功';
            break;
        case 1001:
            $msg = '提交成功';
            break;
        case 1002:
            $msg = '提交失败';
            break;
            //信息填写
        case 1003:
            $msg = '请填写昵称';
            break;
        case 1004:
            $msg = '请填写号码';
            break;
        case 1005:
            $msg = '请填写用户信息';
            break;
            //抽奖活动
        case 1006:
            $msg = '抽奖成功';
            break;
        case 1007:
            $msg = '您已抽过奖了';
            break;
        case 1008:
            $msg = '活动已结束';
            break;
        case 1009:
            $msg = '获取相关信息失败!';
            break;
        case 1010:
            $msg = '分享成功';
            break;
        case 1011:
            $msg = '分享成功，次数+1';
            break;
        case 1012:
            $msg = '';
            break;
        case 1013:
            $msg = '';
            break;
        case 1014:
            $msg = '';
            break;
        case 1015:
            $msg = '';
            break;
        case 1016:
            $msg = '';
            break;
        case 1017:
            $msg = '';
            break;
        case 1018:
            $msg = '';
            break;
        case 1019:
            $msg = '';
            break;
        default: $msg = '请求失败';
    }
    return $msg;
}


