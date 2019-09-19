<?php

/**
 * User: linyangting
 * Date: 2019/8/26
 */
namespace app\games\model\g201908\yxdt;

use think\Db;

class User extends \think\Model
{
    
    public function getUser($openid){
        return Db::table("tp_201908_yxdt_user")->where('openId',$openid)->find();
    }
    
    /**
     * 添加用户信息
     */
    public function addUser($openId,$configs){
        $date = date('Y-m-d H:i:s');
        $data = array(
            'openId'=>$openId,
            'wxName'=>$configs['nickname'],
            'wxPic'=>$configs['headimgurl'],
            'num'=>0,
            'createTime'=>$date
        );
        return Db::table("tp_201908_yxdt_user")->insert($data);
    }
    
    /**
     * 修改用户信息
     */
    public function updateUser($openId,$arr){
        return Db::table("tp_201908_yxdt_user")->where('openId',$openId)->update($arr);
    }
    
    public function setDecUserTimes($openId){
        return Db::table("tp_201908_yxdt_user")->where('openId',$openId)->setDec('times');
    }
    
    public function setIncUserTimes($openId){
        return Db::table("tp_201908_yxdt_user")->where('openId',$openId)->setInc('times');
    }
    
    
}