<?php

/**
 * User: linyangting
 * Date: 2019/09/17
 */
namespace app\games\model\g201909\haiervoting;

use think\Db;

class User extends \think\Model
{
    
    public function getUser($openid){
        return Db::table("tp_201909_haiervoting_user")->where('openId',$openid)->find();
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
            'createTime'=>$date
        );
        return Db::table("tp_201909_haiervoting_user")->insert($data);
    }
    
    /**
     * 修改用户信息
     */
    public function updateUser($openId,$arr){
        return Db::table("tp_201909_haiervoting_user")->where('openId',$openId)->update($arr);
    }
    
    /**
     * 视频列表
     */
    public function getVideoList(){
        return Db::table("tp_201909_haiervoting_video")->select();
    }
    
    /**
     * 图片列表
     */
    public function getPicList(){
        return Db::table("tp_201909_haiervoting_pic")->select();
    }
    
    /**
     * 视频信息
     */
    public function getVideoInfo($id){
        return Db::table("tp_201909_haiervoting_video")->where('id',$id)->find();
    }
    
    /**
     * 图片信息
     */
    public function getPicInfo($id){
        return Db::table("tp_201909_haiervoting_pic")->where('id',$id)->find();
    }
    
    /**
     * 用户投票记录
     * @param unknown $openid
     */
    public function getUserRecord($openid){
        return Db::table("tp_201909_haiervoting_record")->where('openId',$openid)->select();
    }

    /**
     * 新增投票记录
     * @param unknown $voting
     */
    public function insertRecord($voting){
        return Db::table("tp_201909_haiervoting_record")->insert($voting);
    }
    
    
    /**
     * 参赛表 对应用户票数+1（视频）
     * @param unknown $id
     */
    public function addVideoNum($id){
        return Db::table("tp_201909_haiervoting_video")->where('id',$id)->setInc('num');
    }
    
    /**
     * 参赛表 对应用户票数+1（图片）
     * @param unknown $id
     */
    public function addPicNum($id){
        return Db::table("tp_201909_haiervoting_pic")->where('id',$id)->setInc('num');
    }
    
    http://game.vimionline.com/11.mp4
    
    http://game.vimionline.com/gslogo.png

    
}