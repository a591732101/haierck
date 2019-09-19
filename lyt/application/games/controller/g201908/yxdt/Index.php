<?php
/**
 * Author: linyangting
 * Date: 2018/11/28
 */

namespace app\games\controller\g201908\yxdt;
    
use app\games\model\g201908\yxdt\User;
use app\games\model\manage\WxviewConf;
use app\wechat\controller\Newlogin;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Controller
{
    public $openid;						
    public $User;  
    public $projectid = '196';               //项目ID  配置后可用于后台设置  页面信息
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
        if(intval($user['times'])){
            return setjsons('开始游戏',1);
        }
        return setjsons('游戏次数已用完!');
    }
    
    /**
     * 提交答案
     * @param Request $request
     */
    public function submitScore(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        $answer = $request->param('answer','');
        if(!$answer) return setjsons('请提交答案!');
        $answer = explode(',', $answer);
        $res = $this->User->getAnswer();//获取答案
        if(count($answer)!=10) return setjsons('提交答案数有误!');
        $acc = 0;
        foreach ($res as $k =>$v){
            if($v==$answer[$k]['answer']){
                $acc++;
            }
        }
        $data = ['acc'=>$acc];
        $this->User->setDecUserTimes($openid);//游戏次数-1
        $this->User->inserRecord([//新增答题记录
            'openId'=>$openid,
            'acc'=>$acc,
            'createTime'=>date('Y-m-d H:i:s')
        ]);
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
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        if($user['share']){
            return setjsons('您已经分享过了!');
        }
        $this->User->setIncUserTimes($openid);
        return setjsons('分享成功，次数+1',1);
    }
    
}