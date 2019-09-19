<?php
/**
 * Author: linyangting
 * Date: 2019/09/17
 */

namespace app\games\controller\g201909\haiervoting;
    
use app\games\model\g201909\haiervoting\User;
use app\games\model\manage\WxviewConf;
use app\wechat\controller\Newlogin;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Controller
{
    public $openid;
    public $User;
    public $projectid = '201';               //项目ID  配置后可用于后台设置  页面信息
    public $conf;                           //页面设置信息
    
    function __construct(Request $request = null)
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header("Content-type: text/html; charset=utf-8");
        if(strtoupper($_SERVER['REQUEST_METHOD'])== 'OPTIONS'){ 
            return; 
        } 
        parent::__construct($request);
        $this->User = new User();
        $WxviewConf = new WxviewConf();          //页面配置对象类
        $this->conf = $WxviewConf->getConFigById($this->projectid);
    }

    public function login(){
        $login = new  Newlogin;
        $login->login(url("Index"));
    }


    /**
     * 首页
     */
    public function Index(){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        $configs=Session::get('newWechatUser');
        if(!isset($user)){
            $this->User->addUser($openid,$configs);
        }else{
            $arr = array(
                'wxName'=>$configs['nickname'],
                'wxPic'=>$configs['headimgurl']
            );
            $this->User->updateUser($openid,$arr);
        }
        $this->assign('conf', $this->conf);             //传递页面设置信息
        return $this->fetch();
    }
    
    /**
     * 获取首页列表
     */
    public function getGameList(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $type = intval($request->param('type',0));//视频or 图片
        if($type!=1 && $type!=2) return setjsons('参数错误!');
        switch ($type){
            case 1://视频列表
                $res = $this->User->getVideoList();
                break;
            case 2://图片列表
                $res = $this->User->getPicList();
                break;
        }
        return setjsons('',1,['data'=>$res]);        
    }
    
    /**
     * 获取参赛者信息
     * @param Request $request
     */
    public function getGameInfo(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $id = intval($request->param('id'));//作品id
        $type = intval($request->param('type',0));//视频or 图片
        if($type!=1 && $type!=2) return setjsons('参数错误!');
        switch ($type){
            case 1://视频信息
                $res = $this->User->getVideoInfo($id);
                break;
            case 2://图片信息
                $res = $this->User->getPicInfo($id);
                break;
        }
        if(!$res) return setjsons('未查询到用户信息!');
        return setjsons('',1,['data'=>$res]);
    }
    
    /**
     * 投票
     */
    public function voting(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $id = intval($request->param('id'));//每页个数
        $type = intval($request->param('type',0));//视频or 图片
        if($type!=1 && $type!=2) return setjsons('参数错误!');
        switch ($type){
            case 1://视频列表
                $res = $this->User->getVideoInfo($id);
                break;
            case 2://图片列表
                $res = $this->User->getPicInfo($id);
                break;
        }
        if(!$res) return setjsons('未查询到用户信息!');
        $userRecord = $this->User->getUserRecord($openid);//用户投票记录
        if(count($userRecord)>=3){
            return setjsons('抱歉！您的投票次数已用完');
        }
        foreach ($userRecord as $k){
            if($k['openId']==$openid){
                return setjsons('抱歉，您已为该选手投过票了哦！');
            }
        }
        $voting = array(
            'type'      =>  $type,
            'cid'       =>  $id,
            'openId'    =>  $openid,
            'createTime'=>  date('Y-m-d H:i:s'),
            'timestamps'=>  time()
        );
        $this->User->insertRecord($voting);//新增投票记录
        switch ($type){
            case 1://视频列表
                $this->User->addVideoNum($id);//票数+1
                break;
            case 2://图片列表
                $this->User->addPicNum($id);//票数+1
                break;
        }
        return setjsons('投票成功!',1);
    }
    
}