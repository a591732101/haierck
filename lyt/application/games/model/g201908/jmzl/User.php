<?php

/**
 * User: linyangting
 * Date: 2019/8/26
 */
namespace app\games\model\g201908\jmzl;

use think\Db;

class User extends \think\Model
{
    
    public function getUser($openid){
        return Db::table("tp_201908_jmzl_user")->where('openId',$openid)->find();
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
        return Db::table("tp_201908_jmzl_user")->insert($data);
    }
    
    /**
     * 修改用户信息
     */
    public function updateUser($openId,$arr){
        return Db::table("tp_201908_jmzl_user")->where('openId',$openId)->update($arr);
    }
    
    /**
     * 获取游戏信息
     */
    public function getGameInfo(){
        return Db::table("tp_201908_jmzl_info")->find();
    }
    
    /**
     * 获取参赛者的次数
     * @param unknown $openid
     */
    public function getZhuliRecord($openid){
        return Db::table("tp_201908_jmzl_record")->where('can',$openid)->count();
    }
    
    /**
     * 排名
     */
    public function getRank(){
        return Db::table("tp_201908_jmzl_user")->where('nickName','<>','')->filed('wxName,wxPic,num')->order('num desc')->select();
    }
    
    /**
     * 获取当前用户的点亮记录
     * @param unknown $openid
     * @param unknown $nowTime
     */
    public function getUsercanRecord($openid,$nowTime){
        return Db::table("tp_201908_jmzl_record")->where('can',$openid)->where('lightTimes',$nowTime)->select();
    }
    
    /**
     * 获取助力者的点亮记录
     * @param unknown $openid
     * @param unknown $nowTime
     */
    public function getUserzhuRecord($openid,$nowTime){
        return Db::table("tp_201908_jmzl_record")->where('zhu',$openid)->where('lightTimes',$nowTime)->select();
    }
    
    /**
     * 新增游戏助力记录
     * @param unknown $data
     */
    public function insertScoreRcord($data){
        return Db::table("tp_201908_jmzl_record")->insert($data);
    }
    
}