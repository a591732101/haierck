<?php

/**
 * User: linyangting
 * Date: 2019/3/7
 */
namespace app\ding\controller;

use app\ding\model\Auser;
use think\Controller;
// use think\Request;
// use think\Session;

class AdminBase extends Controller{

    protected   $appid = 'dingoahfiy99t8di6eatc8';
    private     $appsecret = "u0kqaUqQpI3LqV04hafpEjPHzc5osVGAlEgpexHT494iVZDeR8kMspluOlnl5RTg";  //秘钥(扫码登录的)
    protected   $User;

    function _initialize (){
        header("Content-type: text/html; charset=utf-8");
        $this->User = new Auser();
        parent::_initialize();
    }

    /**
     * 生产token
     */
    protected function getToken($unionid=''){
        if($unionid=='') return;
        $res = $this->User->getUnionidToken($unionid);//通过$unionid查询token记录表，select
        if($res){
            foreach ($res as $k => $v){
                if(intval($k['overTime']) < intval(time())){
                    $this->User->loginOut($k['token']);
                }
            }
        }
        $token = md5($unionid.time().mt_rand(10,99));
        $arr = array(
            'token'=>$token,
            'unionId'=>$unionid,
            'overTime'=>time()+7200,//有效期2小时
            'times'=>0,
            'updateTime'=>date('Y-m-d H:i:s'),
            'createTime'=>date('Y-m-d H:i:s')
        );
        $this->User->addToken($arr);
        return $token;
    }

    /**
     * 验证token
     */
    protected function valiToken($token){
        if(!isset($token)||$token=='') return ['code'=>-1,'msg'=>'非法登录！'];
        $token = preg_replace('/\s+/', '', $token);
        $res = $this->User->getTokenInfo($token); //通过token查询对应信息
        if($res==null || $res==''){
            return ['code'=>-1,'msg'=>'登录状态已被注销或已被下线！'];
        }
        $now = time();//当前时间戳
        if($now>$res['overTime']){
            return ['code'=>-1,'msg'=>'已超时,请重新登录！'];
        }
        try{
            self::maketoken($token);
        }catch(\Exception $e){
            return ['code'=>-1,'msg'=>$e->getMessage()];
        }
        $user = $this->User->getunUser($res['unionId']);//通过$unionid查询用户信息
        if($user==null || $user==''){
            return ['code'=>-1,'msg'=>'未查询到用户信息!'];
        }
        return ['code'=>1,'data'=>$user];
    }

    /** 
     * 更新token
     */
    private function maketoken($token){
        $res = $this->User->addTimes($token);//次数+1
        $overTime = time()+7200;
        $updateTime = date('Y-m-d H:i:s');
        $arr = ['overTime'=>$overTime,'updateTime'=>$updateTime];
        $tres = $this->User->setTokenInfo($token,$arr);//这个地方有点问题，有的时候返回的居然是0
        if($res){
            return;
        }else{
            throw new \Exception('操作用户信息失败!');
        }
    }

    /**
     * 钉钉登录
     */
    protected function dinglogin($code,$state){
        $access_token = $this->getdingToken();
        $url = 'https://oapi.dingtalk.com/sns/get_persistent_code?access_token='.$access_token;
        $data = json_encode(array(
            'tmp_auth_code' =>  $code
        ));
        $res = json_decode($this->http_post_json($url,$data),true);
        if($res['errcode'] == 0){
            //登录成功
            $unionid = $res['unionid'];
            $user = $this->User->getunUser($unionid);//通过unionid获取用户信息
            if($user!=null && $user!=''){
                $token = $this->getToken($unionid);
                return array('code'=>1,'token'=>$token);
            }
            return array('code'=>0,'errmsg'=>'请先登录钉钉"众行订单应用"');
        }else{
            return array('code'=>2,'errmsg'=>$res['errmsg']);
        }
    }

    /**
     * 获取钉钉access_token
     */
    public function getdingToken(){
        $url = 'https://oapi.dingtalk.com/sns/gettoken';
        $arr = ['appid'=>$this->appid,'appsecret'=>$this->appsecret];
        $res = $this->get_Post_Request($url,$arr);
        $res = json_decode($res,true);
        return $res['access_token'];//$res['access_token']
    }

    /**
     * 根据订单类型设置审核者
     * @return json
     */
    protected function setOrders($type,$ordrtoi){
        $data =  array('code'=>1,'msg'=>'设置成功');
        switch($type['id']){
            case 1:
                $set = ['ordrtoi'=>'?-'.$ordrtoi];
                $this->User->setOrders($type['id'],$set);
                break;
            case 2:
                $set = ['ordrtoi'=>'?-'.$ordrtoi.'-?'];
                $this->User->setOrders($type['id'],$set);
                break;
            case 4:
                $set = ['ordrtoi'=>'?-'.$ordrtoi];
                $this->User->setOrders($type['id'],$set);
                break;
            case 5:
                $set = ['ordrtoi'=>'?-'.$ordrtoi];
                $this->User->setOrders($type['id'],$set);
                break;
            case 6:
                $set = ['ordrtoi'=>'?-'.$ordrtoi];
                $this->User->setOrders($type['id'],$set);
                break;
            case 7:
                $set = ['ordrtoi'=>'?-'.$ordrtoi];
                $this->User->setOrders($type['id'],$set);
                break;
            default:
                $data = array('code'=>0,'msg'=>'设置失败');
        }
        return json($data);
    }

    /**
     * post 请求json参数
     * @return json
     */
    protected function http_post_json($url, $jsonStr){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//不验证证书
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);//状态码 200-成功
        curl_close($ch);
        return $response;
    }

    /**
     * get请求
     */
    protected function get_Post_Request($url = null, $paramArr = []){
        $param = '';
        foreach ($paramArr as $k => $v){
            $param.="$k=$v&";
        }
        $curlPost = substr($param, 0,strlen($param)-1);
        $url =$url.'?'.$curlPost;//url拼接
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//设置这个才能拿到token
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        curl_close($ch);
        return  $output;
    }

    /**
     * 逻辑:
     * 1，拥有查看权限的用户才提供数据:权限<=2，部门领导，审核人，当前用户
     * 2，显示审核的按钮：查看当前订单是否结束，没有结束，再判断当前用户是否为审核者
     * 3，优先级：审核按钮>查看数据
     * @return islook|bool      是否可查看该表单
     * @return nodata|bool      是否获取的数据为空
     * @return data|array       表单数据
     * @return isshenhe|bool    是否当前审核者（显示审核按钮）
     */
    protected function isGuanlianBb($user,$id){
        $department = $user['isLeaderInDepts'];//部门id集
        $userId = $user['userId'];
        $level = intval($user['level']);//权限
        $res = $this->User->getBaobeiInfo($id);//通过id获取报备表信息
        if($res==null||$res==array()){
            return ['isLook'=>false,'nodata'=>true];
        }
        //是否当前审核人

        $isshenhe = false;//默认：不是当前审核人
        if($userId == $res['aflow']&&$res['ispass']==1){
            $isshenhe = true;
        }
        //满足权限需求
        if($level<=2){
            return ['isLook'=>true,'data'=>$res,'isshenhe'=>$isshenhe];
        }
        //是否领导
        $ld = self::str_to_json($department);
        $bb = $res['conmentIds'];//报备表的部门id集
        foreach($ld as $k => $v){
            if($v=='true'){
                if(substr_count($bb,(string)$k)){
                    return ['isLook'=>true,'data'=>$res,'isshenhe'=>$isshenhe];
                }
            }
        }
        //是否当前用户的报备单
        $salesman = $res['salesman'];//表单userid
        if($salesman == $userId){
            return ['isLook'=>true,'data'=>$res,'isshenhe'=>$isshenhe];
        }
        return ['isLook'=>false,'isshenhe'=>$isshenhe];
    }

    /**
     * "{1564361:true,543:false}"类型转数组
     * 字符串转数组
     */
    protected function str_to_json($a){
        $a = substr($a, 1,strlen($a)-2);
        $b = explode(',', $a);
        $arr = array();
        foreach ($b as $k => $v){
            $ks = explode(':', $v);
            $arr[$ks[0]] = $ks[1];
        }
        return $arr;
    }

}