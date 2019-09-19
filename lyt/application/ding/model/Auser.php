<?php

/**
 * User: linyangting
 * Date: 2019/6/11
 */
namespace app\ding\model;

use think\Db;
// use app\ding\controller\Base;

class Auser extends \think\Model
{

    /**--------------------------用户授权---------------------------------------- */

    public function addUser($data){
        return Db::table("tp_dingphone_user")->insert($data);
    }

    public function getUser($userId){
        return Db::table("tp_dingphone_user")->where('userId',$userId)->find();
    }

    public function updateUser($userId,$data){
        return Db::table("tp_dingphone_user")->where('userId',$userId)->update($data);
    }

    /**
     * 通过unionid查询用户信息
     */
    public function getunUser($unionid){
        return Db::table("tp_dingphone_user")->where('unionid',$unionid)->find();
    }

    /**
     * 通过unionid获取token信息
     */
    public function getUnionidToken($unionid){
        return Db::table("tp_ding_admintoken")->where('unionId',$unionid)->select();
    }

    /**
     * 新增token数据
     */
    public function addToken($arr){
        return Db::table("tp_ding_admintoken")->insert($arr);
    }

    /**
     * 删除token数据
     */
    public function loginOut($token){
        return Db::table("tp_ding_admintoken")->where('token',$token)->delete();
    }

    /**
     * 通过unionid更新token数据
     */
    public function updateToken($unionid,$arr){
        return Db::table("tp_ding_admintoken")->where('unionId',$unionid)->update($arr);
    }

    /**
     * token查询对应信息
     */
    public function getTokenInfo($token){
        return Db::table("tp_ding_admintoken")->where('token',$token)->find();
    }

    /**
     * 操作次数+1
     */
    public function addTimes($token){
        return Db::table("tp_ding_admintoken")->where('token',$token)->setInc('times');
    }

    /**
     * 通过token更新token数据
     */
    public function setTokenInfo($token,$arr){
        return Db::table("tp_ding_admintoken")->where('token',$token)->update($arr);
    }

    /**
     * 根据订单类型更改订单流程
     */
    public function setOrders($typeId,$set){
        return Db::table("tp_ding_type")->where('id',$typeId)->update($set);
    }

    /**
     * 通过id获取报备表信息
     */
    public function getBaobeiInfo($id){
        return Db::table("tp_ding_baobei")->where('id',$id)->find();
    }

    /**-------------------------------订单管理----------------------------------------------------- */

    /**
     * 根据订单类型查询 类型表
     */
    public function getDingType($id){
        return Db::table("tp_ding_type")->where('id',$id)->find();
    }

    /**
     * 获取用户列表，过滤:userid，昵称，职位
     */
    public function getUserList(){
        return Db::table("tp_dingphone_user")->field('userId,name,position')->select();
    }

    function getTypeSql($type){
        switch ($type){
            case 1:
                $sqlName = 'tp_ding_ad';
                break;
            case 2:
                $sqlName = 'tp_ding_operation';
                break;
            case 4:
                $sqlName = 'tp_ding_develop';
                break;
            case 5:
                $sqlName = 'tp_ding_daili';
                break;
            case 6:
                $sqlName = 'tp_ding_activity';
                break;
            case 7:
                $sqlName = 'tp_ding_train';
                break;
        }
        return $sqlName;
    }
    
    /**
     * 超级管理员
     * 订单列表,每页10条记录
     * isover 1:未完成  2：以被拒  3：已完成
     */
    public function getAllOrderList($page,$colName,$startTime,$endTime,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['s.id'] = array('LIKE',"%$id%");
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("$sqlName")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        field('s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as isover')->
        where($map)->limit(($page-1)*10,10)->order('s.createTime desc')->select();
    }
    
    /**
     * 超级管理员
     * 数据总数
     */
    public function getAllOCount($colName,$startTime,$endTime,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['colName'] = array('LIKE',"%$colName%");
        $map['id'] = array('LIKE',"%$id%");
        if($startTime!=''||$endTime!=''){
            $map['createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("$sqlName")->where($map)->count();
    }
    
    /**
     * /查询他所在部门的所有订单，以及跟他关联的订单
     */
    public function getSecOrderList($page,$colName,$startTime,$endTime,$department,$userId,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['s.id'] = array('LIKE',"%$id%");
        $bumen = '';
        if(count($department)>1){
            foreach($department as $k =>$v){
                if((count($department)-1) == $k){
                    $bumen .= "s.conmentIds like '%$v%'";
                }else{
                    $bumen .= "s.conmentIds like '%$v%' or ";
                }
            }
        }else{
            $bumen = "s.conmentIds like '%$department[0]%'";
        }
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("$sqlName")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        field('s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as isover')->
        where("$bumen")->where($map)->whereOr('s.flowman','=',$userId)->whereOr('s.salesman','=',$userId)->limit(($page-1)*10,10)->order('s.createTime desc')->select();
    }
    
    /**
     * 管理员的所有订单，以及跟他关联的订单总数
     */
    public function getSecOCount($colName,$startTime,$endTime,$department,$userId,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['s.id'] = array('LIKE',"%$id%");
        $bumen = '';
        if(count($department)>1){
            foreach($department as $k =>$v){
                if((count($department)-1) == $k){
                    $bumen .= "s.conmentIds like '%$v%'";
                }else{
                    $bumen .= "s.conmentIds like '%$v%' or ";
                }
            }
        }else{
            $bumen = "s.conmentIds like '%$department[0]%'";
        }
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("$sqlName")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        where("$bumen")->where($map)->whereOr('s.flowman','=',$userId)->whereOr('s.salesman','=',$userId)->count();
    }
    
    /**
     * 业务员
     * 只能看到自己的订单，和自己相关联的订单
     */
    public function getthrOrderList($page,$colName,$startTime,$endTime,$userId,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['s.id'] = array('LIKE',"%$id%");
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.salesman'] = array('=',$userId);
        return Db::table("$sqlName")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        field('s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as isover')->
        where($map)->whereOr('s.flowman','=',$userId)->limit(($page-1)*10,10)->order('s.createTime desc')->select();
    }
    
    /**
     * 业务员
     * 自己的订单总数
     */
    public function getthrOCount($colName,$startTime,$endTime,$userId,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['s.id'] = array('LIKE',"%$id%");
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.salesman'] = array('=',$userId);
        return Db::table("$sqlName")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        where($map)->whereOr('s.flowman','=',$userId)->count();
    }

    /**
     * 获取订单数据,通过订单类型和ID
     * @param unknown $type
     * @param unknown $id
     */
    public function getOrderInfo($type,$id){
        $sqlName = $this->getTypeSql($type);
        return Db::table("$sqlName")->where('id',$id)->find();
    }

    /**--------------------------------------权限管理-------------------------------------------------- */

    /**
     * 获取权限列表
     *  id,名称，职位，部门
     */
    public function getAllPowerList($page,$size,$name=''){
        return Db::table("tp_dingphone_user")->field('id,name,position,level')->where('name','like',"%$name%")->
            limit(($page-1)*$size,$size)->order('createTime desc')->select();
    }

    /**
     * 获取权限列表总数
     */
    public function getAllPowerCount($name){
        return Db::table("tp_dingphone_user")->where('name','like',"%$name%")->count();
    }

    /**
     * 通过Id修改用户信息
     */
    public function setUserInfoId($id,$arr){
        return Db::table("tp_dingphone_user")->where('id',$id)->update($arr);
    }

    /**---------------------------------------------客户报备-------------------------------------------------- */

    /**
     *  报备列表(超级管理员) 
     */
    public function getBaobeiList($page,$colName,$size,$startTime,$endTime){
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''&&$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_baobei")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        field('s.id,s.typeId,s.industry,s.linkman,s.colName,u.name,s.createTime,case when s.ispass=1 then 1 when s.ispass=2 then 2 ELSE 3 end as ispass')->
        where($map)->limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }

    /**
     *  报备列表总数(超级管理员) 
     */
    public function getAllBaobeiCount($colName,$startTime,$endTime){
        $map['colName'] = array('LIKE',"%$colName%");
        if($startTime!=''&&$endTime!=''){
            $map['createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_baobei")->where($map)->count();
    }

    /**
     * 报备列表(管理员) 
     */
    public function getSectBaobeiList($page,$colName,$size,$startTime,$endTime,$department,$userId){
        $bumen = '';
        if(count($department)>1){
            foreach($department as $k =>$v){
                if((count($department)-1) == $k){
                    $bumen .= "s.conmentIds like '%$v%'";
                }else{
                    $bumen .= "s.conmentIds like '%$v%' or ";
                }
            }
        }else{
            $bumen = "s.conmentIds like '%$department[0]%'";
        }
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_baobei")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        field('s.id,s.typeId,s.industry,s.linkman,s.colName,u.name,s.createTime,case when s.ispass=1 then 1 when s.ispass=2 then 2 ELSE 3 end as ispass')->
        where("$bumen")->where($map)->whereOr('s.salesman','=',$userId)->limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }

    /**
     * 报备列表总数(管理员) 
     */
    public function getSectBaobeiCount($colName,$startTime,$endTime,$department,$userId){
        $bumen = '';
        if(count($department)>1){
            foreach($department as $k =>$v){
                if((count($department)-1) == $k){
                    $bumen .= "s.conmentIds like '%$v%'";
                }else{
                    $bumen .= "s.conmentIds like '%$v%' or ";
                }
            }
        }else{
            $bumen = "s.conmentIds like '%$department[0]%'";
        }
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_baobei")->alias('s')->
        where("$bumen")->where($map)->whereOr('s.salesman','=',$userId)->count();
    }

    /**
     * 报备列表(业务员&个人) 
     */
    public function getthrBaobeiList($page,$colName,$size,$startTime,$endTime,$userId){
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.salesman'] = array('=',$userId);
        return Db::table("tp_ding_baobei")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        field('s.id,s.typeId,s.industry,s.linkman,s.colName,u.name,s.createTime,case when s.ispass=1 then 1 when s.ispass=2 then 2 ELSE 3 end as ispass')->
        where($map)->limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }

    /**
     * 报备列表总数(业务员&个人) 
     */
    public function getthrBaobeiCount($colName,$startTime,$endTime,$userId){
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.salesman'] = array('=',$userId);
        return Db::table("tp_ding_baobei")->alias('s')->where($map)->count();
    }

    /**
     * 查询对应消息表
     * @param type  订单类型
     */
    public function getMessage($id,$type){
        return Db::table("tp_ding_message")->where('orderId',$id)->where('type',$type)->select();
    }

    /**
     * 通过orderId删除订单消息数据
     */
    public function deleteMessageOrder($id){
        return Db::table("tp_ding_message")->where('orderId',$id)->delete();
    }

    /**--------------------------------------排期单--------------------------------------------------- */
    
    /**
     * 获取全部列表(管理员)
     *
     * @param [type] $page 页码
     * @param [type] $colName   客户名
     * @param [type] $startTime 开始时间
     * @param [type] $endTime   结束时间
     * @param [type] $ispass    订单状态 like
     * @return void
     */
    public function getPaiqiList($page,$colName,$startTime,$endTime,$ispass,$count){
        if($startTime!=''&&$startTime!=null&&$endTime!=''&&$endTime!=null){
            $map['createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!=''&&$startTime!=null&&$endTime==''){
            $map['createTime'] = array('>= time',$startTime);
        }else if($endTime!=''&&$endTime!=null&&$startTime==''){
            $map['createTime'] = array('<= time',$endTime.' 23:59:59');
        }
        if($ispass!=''){
            $map['ispass'] = array('=',$ispass);
        }
        $map['colName|project'] = array('like',"%$colName%");
        $map['id'] = array('<>','');
        return Db::table("tp_ding_weixin_paiqidan")->
        where($map)->field('id,colName,project,ispass,createTime')->limit(($count*($page-1)),$count)->order('createTime desc')->select();
    }

    /**
     * 获取总数(管理员)
     *
     * @param [type] $colName
     * @param [type] $startTime
     * @param [type] $endTime
     * @param [type] $ispass
     * @return void
     */
    public function getPaiqiCount($colName,$startTime,$endTime,$ispass){
        if($startTime!=''&&$startTime!=null&&$endTime!=''&&$endTime!=null){
            $map['createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!=''&&$startTime!=null&&$endTime==''){
            $map['createTime'] = array('>= time',$startTime);
        }else if($endTime!=''&&$endTime!=null&&$startTime==''){
            $map['createTime'] = array('<= time',$endTime.' 23:59:59');
        }
        if($ispass!=''){
            $map['ispass'] = array('=',$ispass);
        }
        $map['colName|project'] = array('like',"%$colName%");
        $map['id'] = array('<>','');
        return Db::table("tp_ding_weixin_paiqidan")->where($map)->count();
    }

    /**
     * 获取全部列表(业务员&个人)
     *
     * @param [type] $userId
     * @param [type] $page
     * @param [type] $colName
     * @param [type] $startTime
     * @param [type] $endTime
     * @param [type] $ispass
     * @return void
     */
    public function getselfPaiqiList($userId,$page,$colName,$startTime,$endTime,$ispass,$count){
        if($startTime!=''&&$startTime!=null&&$endTime!=''&&$endTime!=null){
            $map['createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!=''&&$startTime!=null&&$endTime==''){
            $map['createTime'] = array('>= time',$startTime);
        }else if($endTime!=''&&$endTime!=null&&$startTime==''){
            $map['createTime'] = array('<= time',$endTime.' 23:59:59');
        }
        if($ispass!=''){
            $map['ispass'] = array('=',$ispass);
        }
        $map['colName|project'] = array('like',"%$colName%");
        $map['userId'] = array('=',$userId);
        return Db::table("tp_ding_weixin_paiqidan")->
        where($map)->field('id,colName,project,ispass,createTime')->limit(($count*($page-1)),$count)->order('createTime desc')->select();
    }

    /**
     * 获取总数(业务员&个人)
     *
     * @param [type] $userId
     * @param [type] $colName
     * @param [type] $startTime
     * @param [type] $endTime
     * @param [type] $ispass
     * @return void
     */
    public function getselfPaiqiCount($userId,$colName,$startTime,$endTime,$ispass){
        if($startTime!=''&&$startTime!=null&&$endTime!=''&&$endTime!=null){
            $map['createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!=''&&$startTime!=null&&$endTime==''){
            $map['createTime'] = array('>= time',$startTime);
        }else if($endTime!=''&&$endTime!=null&&$startTime==''){
            $map['createTime'] = array('<= time',$endTime.' 23:59:59');
        }
        if($ispass!=''){
            $map['ispass'] = array('=',$ispass);
        }
        $map['colName|project'] = array('like',"%$colName%");
        $map['userId'] = array('=',$userId);
        return Db::table("tp_ding_weixin_paiqidan")->where($map)->count();
    }

    /**
     * 获取排期单
     *
     * @param [type] $id
     * @return void
     */
    public function getPaiqi($id){
        return Db::table("tp_ding_weixin_paiqidan")->where('id',$id)->find();
    }

    /** ---------------------------------------发票----------------------------------------------------*/

    /**
     * 所有发票列表
     */
    public function getInvoiceList($page,$cusName,$size,$taxNumber,$startTime,$endTime,$invoiceName,$name){
        $map['s.taxNumber'] = array('like',"%$taxNumber%");
        if($startTime!=''&&$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.invoiceName'] = array('like',"%$invoiceName%");
        $map['s.salesmanId'] = array('like',"%$name%");
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        field('s.id,s.cusName,s.invoiceName,u.name,s.createTime,s.total,s.accepted,s.orderType,s.isover')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->where($map)->limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }

    /**
     * 所有发票总数
     */
    public function getInvoiceCount($cusName,$taxNumber,$startTime,$endTime){
        $map['s.taxNumber'] = array('like',"%$taxNumber%");
        if($startTime!=''&&$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->where($map)->count();
    }

    /**
     * 对应用户的所有发票
     */
    public function getMyInvoiceList($page,$userId,$cusName,$size,$taxNumber,$startTime,$endTime){
        $map['s.taxNumber'] = array('like',"%$taxNumber%");
        if($startTime!=''&&$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        field('s.id,s.cusName,s.invoiceName,u.name,s.createTime,s.total,s.accepted,s.orderType,s.isover')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->where('s.salesmanId',$userId)->where($map)->
        limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }

    /**
     * 对应用户的所有发票总数
     */
    public function getMyInvoiceCount($userId,$cusName,$taxNumber,$startTime,$endTime){
        $map['s.taxNumber'] = array('like',"%$taxNumber%");
        if($startTime!=''&&$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->where('s.salesmanId',$userId)->where($map)->count();
    }

    /**
     * 获取对应发票所有数据 & 业务员昵称 & 关联的订单id,项目名称,类型
     */
    public function findInvoice($id){
        $res = $this->getInvoice($id);
        $b = new \app\index\controller\Base();
        $sqlName = $b->getInvoiceType($res['orderType']);//对应的订单表名
        $data = explode(',',$res['orderIds']);//关联的订单id集|array
        $arr = array();
        foreach($data as $k => $v){
            $order = $this->getTypeOrderInfo($sqlName,$v);//获取对应订单信息
            $arr[$k]['orderId'] = $v;                   //订单ID
            $arr[$k]['colName'] = $order['colName'];    //订单项目名称
        }
        $idata = array(
            'order'=>$arr,
            'invoiceId'=>$res['id'],
            'cusName'=>$res['cusName'],
            'invoiceName'=>$res['invoiceName'],
            'name'=>$res['name'],
            'total'=>$res['total'],
            'accepted'=>$res['accepted'],
            'orderType'=>$res['orderType'],
            'isover'=>$res['isover'],
            'createTime'=>$res['createTime'],
            'taxNumber'=>$res['taxNumber'],
            'address'=>$res['address'],
            'tel'=>$res['tel'],
            'OBA'=>$res['OBA'],
            'remark'=>$res['remark'],
            'invoiceNumber'=>$res['invoiceNumber'],
            'invoiceTime'=>$res['invoiceTime'],
            'invoiceCash'=>$res['invoiceCash']
        );
        return $idata;
    }

    /**
     * 获取对应发票的收款金额列表
     */
    public function getInvoicePrice($id){
        return Db::table("tp_ding_invoicecash")->where('inId',$id)->field('id,accepted,createTime')->select();
    }

    /**
     * 获取对应发票信息
     */
    public function getInvoice($id){
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        field('s.id,s.cusName,s.invoiceName,s.salesmanId,u.name,s.total,s.orderIds,s.orderType,s.accepted,s.createTime,s.isover,s.taxNumber,
        s.address,s.tel,s.OBA,s.remark,s.invoiceNumber,s.invoiceTime,s.invoiceCash')->
        where('s.id',$id)->find();
    }

    /**
     * 根据 订单种类、id 查询订单信息
     */
    public function getTypeOrderInfo($sqlName,$id){
        return Db::table("$sqlName")->where('id',$id)->find();
    }

    /**
     * 修改订单状态
     */
    public function updateOrderType($id,$sqlName,$type){
        return Db::table("$sqlName")->where('id',$id)->update(array('isover'=>$type));
    }

    /**
     * 删除发票数据
     */
    public function deleteInvoice($id){
        return Db::table("tp_ding_invoice")->where('id',$id)->delete();
    }

    /**
     * 发票 收款详情新增数据
     */
    public function addInvoicePrice($pdata){
        return Db::table("tp_ding_invoicecash")->insert($pdata);
    }

    /**
     * 修改发票数据
     */
    public function updateInvoice($id,$idata){
        return Db::table("tp_ding_invoice")->where('id',$id)->update($idata);
    }

    /**--------------------------------------------------数据导出---------------------------------------------- */

    /**
     * 自有类产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function getzyExcel($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''&&$endTime!=''){
            $startTime = "'".$startTime." 00:00:00'";
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime between $startTime and $endTime";
        }else if($startTime!='' && $endTime==''){
            $startTime = "'".$startTime." 00:00:00'";
            $map = " and s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime <= $endTime";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.databaseType,s.prize,s.sendTime,s.num,s.test,s.remark,s.total,
            i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime as skrq,
            case when s.isover=1 then '订单待审核' when s.isover=2 then '订单被拒' when s.isover=3 then '订单完成（未开发票）' 
            when i.isover=1 then '发票待确认' when i.isover=3 then '发票已确认' ELSE '未知' end as isover";
        return Db::query("SELECT $field FROM tp_ding_ad AS s
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        LEFT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        LEFT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where
        UNION SELECT $field FROM tp_ding_ad AS s 
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        RIGHT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        RIGHT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where");
    }
    
    /**
     * 运营类产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function getyyExcel($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''&&$endTime!=''){
            $startTime = "'".$startTime." 00:00:00'";
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime between $startTime and $endTime";
        }else if($startTime!='' && $endTime==''){
            $startTime = "'".$startTime." 00:00:00'";
            $map = " and s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime <= $endTime";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.prize,s.total,s.startTime,s.endTime,s.remark,
            i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime as skrq,
            case when s.isover=1 then '订单待审核' when s.isover=2 then '订单被拒' when s.isover=3 then '订单完成（未开发票）' 
            when i.isover=1 then '发票待确认' when i.isover=3 then '发票已确认' ELSE '未知' end as isover";
        return Db::query("SELECT $field FROM tp_ding_operation AS s
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        LEFT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        LEFT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where
        UNION SELECT $field FROM tp_ding_operation AS s 
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        RIGHT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        RIGHT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where");
    }
    
    /**
     * 开发类产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function getkfExcel($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''&&$endTime!=''){
            $startTime = "'".$startTime." 00:00:00'";
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime between $startTime and $endTime";
        }else if($startTime!='' && $endTime==''){
            $startTime = "'".$startTime." 00:00:00'";
            $map = " and s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime <= $endTime";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.total,s.serviceItem,s.text,s.cooTimeStart,s.cooTimeEnd,s.psTime,s.gameTime,
        s.remark,s.cost,i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime as skrq,
            case when s.isover=1 then '订单待审核' when s.isover=2 then '订单被拒' when s.isover=3 then '订单完成（未开发票）' 
            when i.isover=1 then '发票待确认' when i.isover=3 then '发票已确认' ELSE '未知' end as isover";
        return Db::query("SELECT $field FROM tp_ding_develop AS s
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        LEFT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        LEFT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where
        UNION SELECT $field FROM tp_ding_develop AS s 
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        RIGHT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        RIGHT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where");
    }
    
    /**
     * 代理广告类产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function getdlExcel($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''&&$endTime!=''){
            $startTime = "'".$startTime." 00:00:00'";
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime between $startTime and $endTime";
        }else if($startTime!='' && $endTime==''){
            $startTime = "'".$startTime." 00:00:00'";
            $map = " and s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime <= $endTime";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.proType,s.total,s.startTime,s.endTime,s.content,s.cost,
        i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime as skrq,
            case when s.isover=1 then '订单待审核' when s.isover=2 then '订单被拒' when s.isover=3 then '订单完成（未开发票）' 
            when i.isover=1 then '发票待确认' when i.isover=3 then '发票已确认' ELSE '未知' end as isover";
        return Db::query("SELECT $field FROM tp_ding_daili AS s
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        LEFT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        LEFT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where
        UNION SELECT $field FROM tp_ding_daili AS s 
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        RIGHT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        RIGHT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where");
    }
    
    /**
     * 活动类产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function gethdExcel($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''&&$endTime!=''){
            $startTime = "'".$startTime." 00:00:00'";
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime between $startTime and $endTime";
        }else if($startTime!='' && $endTime==''){
            $startTime = "'".$startTime." 00:00:00'";
            $map = " and s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime <= $endTime";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.total,s.prize,s.startTime,s.endTime,s.context,s.remark,s.cost,
        i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime as skrq,
            case when s.isover=1 then '订单待审核' when s.isover=2 then '订单被拒' when s.isover=3 then '订单完成（未开发票）' 
            when i.isover=1 then '发票待确认' when i.isover=3 then '发票已确认' ELSE '未知' end as isover";
        return Db::query("SELECT $field FROM tp_ding_activity AS s
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        LEFT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        LEFT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where
        UNION SELECT $field FROM tp_ding_activity AS s 
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        RIGHT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        RIGHT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where");
    }
    
    /**
     * 12306产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function gettrExcel($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''&&$endTime!=''){
            $startTime = "'".$startTime." 00:00:00'";
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime between $startTime and $endTime";
        }else if($startTime!='' && $endTime==''){
            $startTime = "'".$startTime." 00:00:00'";
            $map = " and s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $endTime = "'".$endTime." 23:59:59'";
            $map = " and s.createTime <= $endTime";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.total,s.prize,s.sendTime,s.num,s.linkman,s.comNumber,s.remark,s.cost,
            i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime as skrq,
            case when s.isover=1 then '订单待审核' when s.isover=2 then '订单被拒' when s.isover=3 then '订单完成（未开发票）' 
            when i.isover=1 then '发票待确认' when i.isover=3 then '发票已确认' ELSE '未知' end as isover";
        return Db::query("SELECT $field FROM tp_ding_train AS s
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        LEFT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        LEFT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where
        UNION SELECT $field FROM tp_ding_train AS s 
        LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId
        RIGHT JOIN tp_ding_invoice AS i ON s.invoiceId = i.id
        RIGHT JOIN tp_ding_invoicecash AS c ON i.id = c.inId $where");
    }


}