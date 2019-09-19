<?php
/**
 * Author: linyangting
 * Date: 2018/11/28
 */

namespace app\games\controller\g201908\yjlllk;
    
use app\games\model\g201908\yjlllk\User;
use app\games\model\manage\WxviewConf;
use app\wechat\controller\Newlogin;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Controller
{
    public $openid;						
    public $User;  
    public $projectid = '195';               //项目ID  配置后可用于后台设置  页面信息
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
     * 开始游戏
     * @return void|Json
     */
    public function playGames(){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        $data = ['score'=>$user['score']];
        if(intval($user['times'])){
            return setjsons('开始游戏',1,$data);
        }
        return setjsons('游戏次数已用完!',0,$data);
    }
    
    /**
     * 提交分数
     * @param Request $request
     */
    public function submitScore(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        $time = $request->param('time',0);
        $score = intval($request->param('score',0));
        if($time==0) return setjsons('提交分数有误!');
        if($user['times']==0){
            return setjsons('抱歉，您的次数已用完，分享次数可+1');
        }
        $oldtime = $user['time'];//历史时间
        $oldscore = $user['score'];//历史分数
        $data = array(
            'highScore'=>$oldscore,
            'lowTime' =>$oldtime
        );
        if($score>$oldscore){
            $this->User->updateUser($openid, array('score'=>$score,'time'=>$time));
            $data = array(
                'highScore'=>$score,
                'lowTime' =>$time
            );
        }else if($score==$oldscore){
            if($time<$oldtime){
                $this->User->updateUser($openid, array('time'=>$time));
            }
            $data['lowTime'] = $time;
        }
        $this->User->setDecUserTimes($openid);//游戏次数-1
        return setjsons('提交成功',1,$data);
    }
    
    /**
     * 填写用户信息
     * @param Request $request
     */
    public function setUserInfo(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        if($user['nickName']!=''&&$user['tel']!=''){
            return setjsons('您已提交用户信息!');
        }
        $nickName = $request->param('nickName','');
        $tel = $request->param('tel','');
        if($nickName==''&&$tel==''){
            return setjsons('用户信息不能为空');
        }else if(count($tel)!=11){
            return setjsons('电话号码长度错误');
        }
        $arr = array('nickName'=>$nickName,'tel'=>$tel);
        $this->User->updateUser($openid, $arr);
        return setjsons('提交成功',1);
    }
    
    /**
     * 分享
     * @return Json
     */
    public function share(){
        $openid = Session::get('newWechatUser')['openid'];
        $this->User->setIncUserTimes($openid);
        return setjsons('分享成功，次数+1',1);
    }
    
}