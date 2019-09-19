<?php

/**
 * User: linyangting
 * Date: 2019/6/12
 */
namespace app\index\model;

use think\Db;
use app\index\controller\Base;

class User extends \think\Model
{

    /**
     * 数据列表
     */
    public function getUInfoss($page){
        $page = 15*($page-1);
        return Db::table("user")->where('nickName','<>','')->limit($page,15)->select();
    }
    
    /**
     * 数据总数
     */
    public function getUInfoCountss(){
        return Db::table("user")->where('nickName','<>','')->count();
    }
    
    public function getExcelgetExcelss(){
        return Db::table("user")->where('nickName','<>','')->select();
    }
    
    /**------------------------*/
    
    public function getUss($page){
        $page = 15*($page-1);
        return Db::table("user")->alias('u')->join('sheet s','u.openId= s.openId','right')->
        field('u.id,u.wxName,u.wxPic,u.sex,u.nickName,u.age,u.company,u.position,u.tel,u.wxNumber,u.likes,u.expect,s.picUrl')->
        limit($page,15)->select();
    }
    
    /**
     * 数据总数
     */
    public function getUtss(){
        return Db::table("sheet")->count();
    }
    
    public function gett($startTime,$endTime){
        if($startTime!='' && $endTime!=''){
            $map['createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['id'] = array('<>','');
        return Db::table("user")->field('id,createTime')->where($map)->order('createTime desc')->select();
        
//     'SELECT s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as type
//             FROM tp_ding_ad AS a LEFT JOIN tp_dingphone_user AS u ON s.salesman = u.userId where ';
    }
    
    public function getWeekSignCount($openId,$date){
        return Db::table("sign")->where('openId',$openId)->where('createTime',$date)->count();
    }
    
    public function addsign($arr){
        return Db::table("sign")->insert($arr);
    }
    
    public function getWeekSignList($openId,$date){
        $map['openId'] = array('=',$openId);
        $map['createTime'] = array('EGT',$date);
        
        return Db::table("sign")->field('week,substring_index(createTime,"",10)')->where($map)->order('createTime asc')->select();
    }
    
    /**--------------------------------------------数据导出-------------------------------------------------*/
    
    
    /**
     * 自有类产品
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    public function getzyExcel($id,$colName,$name,$startTime,$endTime){
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('like',"%$id%");
        $map['s.salesman'] = array('like',"%$name%");
        return Db::table("tp_ding_ad")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        join(["tp_ding_invoice" => "i"],'s.invoiceId = i.id','LEFT')->
        field('s.id,s.createTime,s.colName,u.name,s.databaseType,s.prize,s.sendTime,s.num,s.test,s.remark,s.total,
            i.invoiceNumber as pid,i.cusName,i.total,i.invoiceTime,i.invoiceCash')->
        where($map)->order('s.createTime desc')->select();
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
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('like',"%$id%");
        $map['s.salesman'] = array('like',"%$name%");
        return Db::table("tp_ding_operation")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        join(["tp_ding_invoice" => "i"],'s.invoiceId = i.id','LEFT')->
        field('s.id,s.createTime,s.colName,u.name,s.prize,s.total,s.startTime,s.endTime,s.remark,
            i.invoiceNumber as pid,i.cusName,i.total,i.invoiceTime,i.invoiceCash')->
                    where($map)->order('s.createTime desc')->select();
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
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('like',"%$id%");
        $map['s.salesman'] = array('like',"%$name%");
        return Db::table("tp_ding_develop")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        join(["tp_ding_invoice" => "i"],'s.invoiceId = i.id','LEFT')->
        field('s.id,s.createTime,s.colName,u.name,s.total,s.serviceItem,s.text,s.cooTimeStart,s.cooTimeEnd,s.psTime,s.gameTime,
            s.remark,s.cost,i.invoiceNumber as pid,i.cusName,i.total,i.invoiceTime,i.invoiceCash')->
                where($map)->order('s.createTime desc')->select();
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
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('like',"%$id%");
        $map['s.salesman'] = array('like',"%$name%");
        return Db::table("tp_ding_daili")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        join(["tp_ding_invoice" => "i"],'s.invoiceId = i.id','LEFT')->
        field('s.id,s.createTime,s.colName,u.name,s.proType,s.total,s.startTime,s.endTime,s.content,s.cost,
            i.invoiceNumber as pid,i.cusName,i.total,i.invoiceTime,i.invoiceCash')->
                where($map)->order('s.createTime desc')->select();
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
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('like',"%$id%");
        $map['s.salesman'] = array('like',"%$name%");
        return Db::table("tp_ding_activity")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        join(["tp_ding_invoice" => "i"],'s.invoiceId = i.id','LEFT')->
        field('s.id,s.createTime,s.colName,u.name,s.total,s.prize,s.startTime,s.endTime,s.context,s.remark,s.cost,
            i.invoiceNumber as pid,i.cusName,i.total,i.invoiceTime,i.invoiceCash')->
                where($map)->order('s.createTime desc')->select();
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
        $map['s.colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['s.createTime'] = array('between time',[$startTime,$endTime.' 23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['s.createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['s.createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('like',"%$id%");
        $map['s.salesman'] = array('like',"%$name%");
        return Db::table("tp_ding_train")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesman = u.userId','LEFT')->
        join(["tp_ding_invoice" => "i"],'s.invoiceId = i.id','LEFT')->
        field('s.id,s.createTime,s.colName,u.name,s.total,s.prize,s.sendTime,s.num,s.linkman,s.comNumber,s.remark,s.cost,
            i.invoiceNumber as pid,i.cusName,i.total,i.invoiceTime,i.invoiceCash')->
                where($map)->order('s.createTime desc')->select();
    }
    
    function sql($id,$colName,$name,$startTime,$endTime){
        $map = '';
        if($startTime!=''||$endTime!=''){
            $map = " and s.createTime between $startTime and $endTime.' 23:59:59'";
        }else if($startTime!='' && $endTime==''){
            $map = "s.createTime >= $startTime";
        }else if($startTime=='' && $endTime!=''){
            $map = "s.createTime <= $endTime.' 23:59:59'";
        }
        $where = "where s.colName LIKE '%$colName%' and s.id LIKE '%$id%' and 
        s.salesman LIKE '%$name%' $map";
        $field = "s.id,s.createTime,s.colName,u.name,s.databaseType,s.prize,s.sendTime,s.num,s.test,s.remark,s.total,
            i.invoiceNumber as pid,i.cusName,i.invoiceCash,i.invoiceTime,c.accepted,c.createTime
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
    
    /**--------------------------------------------------------------------------------------------------*/
    
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
        field('s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as type')->
        where($map)->limit(($page-1)*10,10)->order('s.createTime desc')->select();
    }
    
    /**
     * 超级管理员
     * 数据总数
     */
    public function getAllOCount($colName,$startTime,$endTime,$type,$id){
        $sqlName = $this->getTypeSql($type);
        $map['colName'] = array('LIKE',"%$colName%");
        if($startTime!=''||$endTime!=''){
            $map['createTime'] = array('between time',[$startTime,$endTime.'23:59:59']);
        }else if($startTime!='' && $endTime==''){
            $map['createTime'] = array('>=',$startTime);
        }else if($startTime=='' && $endTime!=''){
            $map['createTime'] = array('<=',$endTime.' 23:59:59');
        }
        $map['s.id'] = array('LIKE',"%$id%");
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
        field('s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as type')->
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
        field('s.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as type')->
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
    
    /**----------------------------------------------发票管理------------------------------------------------------------*/
    
    /**
     * 所有发票列表
     */
    public function getInvoiceList($page,$cusName,$size){
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        field('s.id,s.cusName,s.invoiceName,u.name,s.createTime,s.total,s.accepted,s.orderType,s.isover')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }
    
    /**
     * 所有发票总数
     */
    public function getInvoiceCount($cusName){
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->count();
    }
    
    /**
     * 对应用户的所有发票
     */
    public function getMyInvoiceList($page,$userId,$cusName,$size){
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        field('s.id,s.cusName,s.invoiceName,u.name,s.createTime,s.total,s.accepted,s.orderType,s.isover')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->where('s.salesmanId',$userId)->
        limit(($page-1)*$size,$size)->order('s.createTime desc')->select();
    }
    
    /**
     * 对应用户的所有发票总数
     */
    public function getMyInvoiceCount($userId,$cusName){
        return Db::table("tp_ding_invoice")->alias('s')->join(["tp_dingphone_user" => "u"],'s.salesmanId = u.userId','LEFT')->
        where('s.cusName&s.invoiceName','like',"%$cusName%")->where('s.salesmanId',$userId)->count();
    }
    
    /**
     * 获取对应发票所有数据 & 业务员昵称 & 关联的订单id,项目名称,类型
     */
    public function findInvoice($id){
        $res = $this->getInvoice($id);
        $b = new Base();
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
    
    public function aw(){
        return Db::table("tp_201907_yjldt_tiku")->where('id',75616841)->
//         field('id,question,A,B,C,D,music')
        field('answer')->find();
    }
    
    
    public function getMessaheInfo(){
        $field = 's.id,s.typeId,s.colName,u.name,s.createTime,case when s.isover=1 then 1 when s.isover=2 then 2 ELSE 3 end as isover,
            s.changeCash';
        "select $field from tp_ding_ad as s left join tp_dingphone_user as u on s.salesmanId = u.userId where 
        union
         select $field from tp_ding_ad as s left join tp_dingphone_user as u on s.salesmanId = u.userId where";
    }
    
}