<?php
/**
 * Author: linyangting
 * Date: 2018/11/28
 */

namespace app\games\controller\g201908\jmzl;
    
use app\games\model\g201908\jmzl\User;
use app\games\model\manage\WxviewConf;
use app\wechat\controller\Newlogin;
use app\games\Utils;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Controller
{
    public $openid;						
    public $User;  
    public $projectid = '194';               //项目ID  配置后可用于后台设置  页面信息
    public $conf;                           //页面设置信息
    public $pmsg;
    
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
        $this->pmsg = new \app\games\Utils\Utils();
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
     * 获取参与人数
     */
    public function getPeopleCount(){
        $people = $this->User->getGameInfo();//昵称和电话号码不为空的用户总数
        return setjsons('请求成功',1,[people => $people['peopleNum']]);
    }
    
    /**
     * 开始助力
     * @return void|Json
     */
    public function playGames(){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        if($user['nickName']==''){
            return setjsons('请填写用户信息',2);
        }
        $count = $this->User->getZhuliRecord($openid);//获取参赛者的次数
        $msg = $this->getpmessage($count);//提示语
        return setjsons($msg,1,['count'=>$count]);
    }
    
    /**
     * 填写用户信息（参赛）
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
        return setjsons('参赛成功',1);
    }
    
    /**
     * 排行榜
     * @return Json
     */
    public function rank(){
        $res = $this->User->getRank();//查询nickName不为空的用户排名
        return setjsons('',1,['data'=>$res]);
    }

    /**
     * 获取提示语
     */
    function getpmessage($count = 0){
        $c = 0;
        if($count<24){
            $c = 24-$count;
        }
        $msg = "还差 $c 个好友助力就可以点亮全部动物了!";
        if($count>=6&&$count<12){
            $msg = '恭喜你获得精美公仔一份!';
        }else if($count>=12&&$count<24){
            $msg = '恭喜你获得五谷杂粮一份!';
        }else if($count>=24){
            $msg = '恭喜你，获得价值两百元哈根达斯代金券!';
        }
        return $msg;
    }
    
    /**
     * 分享页
     */
    public function Share(Request $request){
        $o = $request->param('o','');//参赛者OPENID
        if($o!=''){
            Session::set('o',$o);
        }
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
        $o = Session::pull('o');
        $this->assign('o', $o);
        $this->assign('conf', $this->conf);             //传递页面设置信息
        return $this->fetch();
    }
    
    /**
     * 获取点亮的数据
     * @param Request $request
     */
    public function getlight(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        $o = $request->param('o','');
        if($o==''){
            return setjsons('获取被助力者用户信息失败!');
        }
        $count = $this->User->getZhuliRecord($o);
        if($count>=24){
            $count = 24;
        }
        return setjsons('',1,['count'=>$count]);
    }
    
    /**
     * 点亮
     * @param Request $request
     */
    public function friendHelp(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $user = $this->User->getUser($openid);
        $o = $request->param('o','');//参赛者
        if($o==''){
            return setjsons('获取被助力者用户信息失败!');
        }
        $nowTime = strtotime(date('Y-m-d'));//当天时间戳
        $res = $this->User->getUsercanRecord($o,$nowTime);//获取参赛者的点亮记录
        foreach ($res as $k){
            if($k['zhu']==$openid){
                return setjsons('抱歉！您今天已为该用户助力过了');
            }
        }
        $count = $this->User->getUserzhuRecord($openid,$nowTime);//获取助力者的点亮记录
        if(count($count)>=10){
            return setjsons('抱歉！您今日助力次数已达到上限');
        }
        $data = array(
            'zhu' =>   $openid,
            'can' =>    $o,
            'createTime' =>date('Y-m-d H:i:s'),
            'lightTimes' =>$nowTime
        );
        $this->User->insertScoreRcord($data);//新增游戏助力记录
        return setjsons('助力成功',1);
    }
    
    public function test(){
        
    }
    
    
}