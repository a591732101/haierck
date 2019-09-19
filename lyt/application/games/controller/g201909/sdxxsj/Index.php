<?php
/**
 * Author: linyangting
 * Date: 2018/11/28
 */

namespace app\games\controller\g201909\sdxxsj;
    
use app\games\model\g201909\hqvoting\User;
use app\games\model\manage\WxviewConf;
use app\wechat\controller\Newlogin;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Controller
{
    public $openid;
    public $User;
    public $projectid = '200';               //项目ID  配置后可用于后台设置  页面信息
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
        $this->assign('conf', $this->conf);             //传递页面设置信息
        return $this->fetch();
    }
    

    /**
     * 参赛(群主)
     */
    public function joinGameZhu(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $configs=Session::get('newWechatUser');
        $nickName = $request->param('nickName','');
        $tel = $request->param('tel','');
        $village = $request->param('village','');
        $room = $request->param('room','');
        $likes = $request->param('likes','');
        if(!$nickName){
            return setjsons('昵称不能为空');
        }else if(!$tel){
            return setjsons('电话不能为空');
        }else if(strlen($tel)!=11){
            return setjsons('电话号码长度错误');
        }else if(!$village){
            return setjsons('请填写小区');
        }else if(!$room){
            return setjsons('请填写楼栋与房号');
        }else if(!$likes){
            return setjsons('请填写要创办的群');
        }
        $arr = array(
            'openId'=>$openid,
            'wxName'=>$configs['nickname'],
            'wxPic'=>$configs['headimgurl'],
            'nickName' => $nickName,
            'tel' => $tel,
            'village' => $village,
            'room' => $room,
            'likes' => $likes,
            'createTime'=>date('Y-m-d H:i:s')
        );
        $this->User->insertUserZhu($arr);//新增参赛数据
        return setjsons('提交群主信息成功',1);
    }
    
    /**
     * 参赛(群员)
     */
    public function joinGameYuan(Request $request){
        $openid = Session::get('newWechatUser')['openid'];
        if (!isset($openid)) {
            $this->redirect(url('login'));
            return;
        }
        $configs=Session::get('newWechatUser');
        $nickName = $request->param('nickName','');
        $tel = $request->param('tel','');
        $village = $request->param('village','');
        $room = $request->param('room','');
        $likes = $request->param('likes','');
        if(!$nickName){
            return setjsons('昵称不能为空');
        }else if(!$tel){
            return setjsons('电话不能为空');
        }else if(strlen($tel)!=11){
            return setjsons('电话号码长度错误');
        }else if(!$village){
            return setjsons('请填写小区');
        }else if(!$room){
            return setjsons('请填写楼栋与房号');
        }else if(!$likes){
            return setjsons('请填写要创办的群');
        }
        $arr = array(
            'openId'=>$openid,
            'wxName'=>$configs['nickname'],
            'wxPic'=>$configs['headimgurl'],
            'nickName' => $nickName,
            'tel' => $tel,
            'village' => $village,
            'room' => $room,
            'likes' => $likes,
            'createTime'=>date('Y-m-d H:i:s')
        );
        $this->User->insertUseryuan($arr);//新增参赛数据
        return setjsons('提交群员信息成功',1);
    }
    
    
    
}