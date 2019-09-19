<?php

/**
 * 管理端
 * User: linyangting
 * Date: 2019/3/7
 */
namespace app\ding\controller;

use think\Request;
// use app\ding\controller\Index;
use think\Session;

class Admin extends AdminBase{


    public function Login(){
        return $this->fetch();
    }

    public function Index(){
        return $this->fetch();
    }


    /**----------------------------------登录授权-------------------------------------------- */

    /**
     * 扫码登录验证用户信息
     */
    public function logins(Request $req){
        $code = $req->param('code');
        $state = $req->param('state');
        if(!isset($code)||$code==null){
            return $this->error('验证失败！');
        }
        $res = parent::dinglogin($code,$state);
        if($res['code']==1){
            $token = $res['token'];
            Session::set('token',$token);
            $url = 'https://aiwei.weilot.com/ding/admin/login#/loginJump?t='.$token;
            header("location:$url");
            exit;
        }else{
            $errmsg = $res['errmsg'];
            echo "<script>alert($errmsg)</script>";
            $url = 'https://aiwei.weilot.com/ding/admin/login#/login';
            echo "<meta http-equiv='Refresh' content=4; URL=$url>";
            // header("location:$url");
            return;
        }
    }

    /**
     * ######
     * token返回给前端
     */
    public function isusert(){
        $token = Session::pull('token');
        if(!isset($token) || $token=='' || $token==null){
            return json(['t'=>'']);
        }
        return json(['t'=>$token]);
    }

    /**
     * ######
     * 获取登录的用户信息
     */
    public function getLoginUserinfo(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $arr = array(
            'name'=>$res['data']['name'],
            'position'=>$res['data']['position'],
            'avatar'=>$res['data']['avatar']
        );
        return json(['code'=>1,'data'=>$arr]);
    }

    /**
     * 注销
     */
    public function loginOut(Request $req){
        $token = $req->param('token');
        $res = parent::valiToken($token);
        if($res['code']==-1) return json($res);
        $login = $this->User->loginOut($token);
        if($login){
            return json(['code'=>1,'msg'=>'注销成功！']);
        }else{
            return setjsons('注销失败！');
        }
    }

    /**------------------------------订单管理----------------------------------------- */

    /**
     * 获取订单权限列表
     */
    public function getOrderPowerList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);

        $typeId = intval($req->param('typeId'));
        $type = $this->User->getDingType($typeId);//通过订单类型获取对应订单流程信息
        if($type==null || $type==''){
            return setjsons('请输入正确的订单类型');
        }
        $level = $res['data']['level'];//用户权限
        if($level>1) return setjsons('抱歉，该用户没有权限查看！');
        $ordrtoi = $type['ordrtoi'];//订单流程
        $orarr = explode('-',$ordrtoi);
        $arr = [];
        foreach($orarr as $k =>$v){
            if($k==0){
                array_push($arr,array(
                    'index'=>$k,
                    'isUpdate'=>0,
                    'name'=>'部门经理'
                ));
                continue;
            }else if($v=='?'){
                array_push($arr,array(
                    'index'=>$k,
                    'isUpdate'=>0,
                    'name'=>'指定人员'
                ));
                continue;
            }else{
                $user = $this->User->getUser($v);//通过userId查询对应的
                array_push($arr,array(
                    'index'=>$k,
                    'isUpdate'=>1,
                    'name'=>$user['name']
                ));
                continue;
            }
        }
        return json(['code'=>1,'msg'=>'操作成功','data'=>$arr]);
    }

    /**
     * 获取用户列表(修改审核者)
     */
    public function getUserList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $res = $this->User->getUserList();//获取用户列表，过滤:userid，昵称，职位
        return json($res);
    }

    /**
     * 设置审核人员
     */
    public function setOrderPeople(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $user = $res['data'];
        if($user['level']!=1) return setjsons('抱歉！您无权限设置!');
        $typeId = intval($req->param('typeId'));
        $type = $this->User->getDingType($typeId);//通过订单类型获取对应订单流程信息
        if($type==null || $type=='') return setjsons('请输入正确的订单类型');
        //审核流 格式：65213514-74614 or 46312321,审核流只提交可以改的审核
        $ordrtoi = $req->param('ordrtoi');
        if(!isset($ordrtoi)||$ordrtoi==''||$ordrtoi==null)return setjsons('审核者不能为空');
        $ordrtoiarr= explode('-',$ordrtoi);
        foreach($ordrtoiarr as $k => $v){
            $res = $this->User->getUser($v);
            if($res==null||$res==''||!isset($res)){
                return setjsons('抱歉!未查询到指定的审核者');
            }
        }
        $result = parent::setOrders($type,$ordrtoi);
        return $result;
    }

    /**
     * 订单列表
     * @param Request $req
     */
    public function getOrderList(Request $req){
        $res = parent::valiToken($req->param('token',''));
        if($res['code']==-1) return json($res);
        $page = intval($req->param('page'));//页码
        $type = intval($req->param('type'));//订单类型
        $colName = $req->param('colName','');//客户名称（项目名称）
        $colName = preg_replace('/\s+/', '', $colName);
        $startTime = $req->param('startTime','');//开始时间
        $endTime = $req->param('endTime','');//结束时间
        $id = $req->param('id','');//订单id
        //TODO 请求过滤参数
        if(!$page) $page = 1;
        if(!$type) return setjsons('请选择订单类型!');
        $level = intval($res['data']['level']);//权限
        switch($level){
            case 1://最高权限
                $result = $this->User->getAllOrderList($page,$colName,$startTime,$endTime,$type,$id);//订单列表,每页10条记录
                $totalPage = $this->User->getAllOCount($colName,$startTime,$endTime,$type,$id);    //数据总数
                $pageCount = ceil($totalPage/10);//总页数
                $data = array(
                    'data'=>$result,           //数据列表
                    'nowPage'=>$page,       //当前页0
                    'count'=>$pageCount,//总页数
                    'size'=>$totalPage //数据量
                );
                break;
            case 2://管理员权限
                $department = explode(',',$res['data']['department']);//用户部门id集
                $result = $this->User->getSecOrderList($page,$colName,$startTime,$endTime,$department,$res['data']['userId'],$type,$id);//查询他所在部门的所有订单，以及跟他关联的订单
                $totalPage = $this->User->getSecOCount($colName,$startTime,$endTime,$department,$res['data']['userId'],$type,$id);    //数据总数
                $pageCount = ceil($totalPage/10);//总页数
                $data = array(
                    'data'=>$result,           //数据列表
                    'nowPage'=>$page,       //当前页
                    'count'=>$pageCount,//总页数
                    'size'=>$totalPage //数据量
                );
                break;
            default://业务员权限 & 普通权限(只能看到自己的订单，和自己相关联的订单)
                $result = $this->User->getthrOrderList($page,$colName,$startTime,$endTime,$res['data']['userId'],$type,$id);
                $totalPage = $this->User->getthrOCount($colName,$startTime,$endTime,$res['data']['userId'],$type,$id);    //数据总数
                $pageCount = ceil($totalPage/10);//总页数
                $data = array(
                    'data'=>$result,           //数据列表
                    'nowPage'=>$page,       //当前页
                    'count'=>$pageCount,//总页数
                    'size'=>$totalPage //数据量
                );
                break;
        }
        return json(['code'=>1,'msg'=>'请求成功!','data'=>$data]);
    }

    /**
     * 获取对应订单详情
     */
    public function getOrderInfo(Request $request){
        $res = parent::valiToken($request->param('token'));
        if($res['code']==-1) return json($res);
        $id = $request->param('id');        //订单id
        $type = intval($request->param('type'));    //订单类型
        if(!isset($id)||$id=='') return setjsons('订单参数不能为空!');
        if(!$type) return setjsons('订单参数不能为空!');
        $res = $this->User->getOrderInfo($type,$id);//获取订单数据,通过订单类型和ID
        $user = $this->User->getUser($res['salesman']);
        $res['name'] = $user['name'];
        return json(['code'=>1,'msg'=>'操作成功','data'=>$res]);
    }

    /**------------------------------------权限管理------------------------------------------ */

    /**
     * 获取权限列表
     */
    public function getPowerList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $page = intval($req->param('page'));//页码
        if($page==0) $page = 1;
        $size = intval($req->param('size'));//每页数量
        if($size==0) $size = 10;
        $name = $req->param('name');//职员名称
        $result = $this->User->getAllPowerList($page,$size,$name);  //权限列表,id,名称，职位，部门
        $totalPage = $this->User->getAllPowerCount($name);       //数据总数
        $pageCount = ceil($totalPage/$size);//总页数
        $data = array(
            'data'=>$result,           //数据列表
            'nowPage'=>$page,       //当前页
            'pageCount'=>$pageCount,//总页数
            'totalPage'=>$totalPage //数据量
        );
        return json(['code'=>1,'msg'=>'操作成功','data'=>$data]);
    }

    /**
     * 设置权限
     */
    public function setPower(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $user = $res['data'];
        if($user['level']!=1) return setjsons('抱歉！您无权限设置!');
        $id = intval($req->param('id'));//用户id,非userid
        if($id==0) return setjsons('请选择正确的用户!');
        $level = intval($req->param('level'));
        if(!in_array($level,[1,2,3,4])) return setjsons('请设置正确的权限');
        $arr = array('level'=>$level);
        $this->User->setUserInfoId($id,$arr);//通过Id修改用户信息
        return json(['code'=>1,'msg'=>'设置成功!']);
    }

    /**----------------------------客户报备--------------------------------- */

    /**
     * 报备单列表
     */
    public function getBaobeiList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $page = intval($req->param('page'));//页码
        if($page==0) $page = 1;
        $size = intval($req->param('size'));//每页数量
        if($size==0) $size = 10;
        $colName = $req->param('colName');//职员名称
        $startTime = $req->param('startTime');//开始时间
        $endTime = $req->param('endTime');//结束时间
        $level = $res['data']['level'];//权限
        switch($level){
            case 1://最高权限
                $res = $this->User->getBaobeiList($page,$colName,$size,$startTime,$endTime);//报备列表,每页10条记录
                $totalPage = $this->User->getAllBaobeiCount($colName,$startTime,$endTime);    //数据总数
                $pageCount = ceil($totalPage/$size);//总页数
                $data = array(
                    'data'=>$res,           //数据列表
                    'nowPage'=>$page,       //当前页
                    'pageCount'=>$pageCount,//总页数
                    'totalPage'=>$totalPage //数据量
                );
            break;
            case 2://管理员权限
                $department = explode(',',$res['data']['department']);//用户部门id集
                $result = $this->User->getSectBaobeiList($page,$colName,$size,$startTime,$endTime,$department,$res['data']['userId']);//查询他所在部门的所有订单，以及跟他关联的订单
                $totalPage = $this->User->getSectBaobeiCount($colName,$startTime,$endTime,$department,$res['data']['userId']);    //数据总数
                $pageCount = ceil($totalPage/$size);//总页数
                $data = array(
                    'data'=>$result,           //数据列表
                    'nowPage'=>$page,       //当前页
                    'pageCount'=>$pageCount,//总页数
                    'totalPage'=>$totalPage //数据量
                );
            break;
            default://业务员权限 & 普通权限(只能看到自己的订单，和自己相关联的订单)
                $result = $this->User->getthrBaobeiList($page,$colName,$size,$startTime,$endTime,$res['data']['userId']);
                $totalPage = $this->User->getthrBaobeiCount($colName,$startTime,$endTime,$res['data']['userId']);    //数据总数
                $pageCount = ceil($totalPage/$size);//总页数
                $data = array(
                    'data'=>$result,          //数据列表
                    'nowPage'=>$page,       //当前页
                    'pageCount'=>$pageCount,//总页数
                    'totalPage'=>$totalPage //数据量
                );
            break;
        }
        return json(['code'=>1,'msg'=>'操作成功','data'=>$data]);
    }


    /**
     * 获取报备单详情
     */
    public function getBaobeiInfo(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $id = $req->param('id');  //报备id
        if(!isset($id)||$id=='') return setjsons('参数不能为空!');
        $item = parent::isGuanlianBb($res['data'],$id);//该用户是否可以查看该报备单
        if(!$item['isLook']){
            if(isset($item['nodata'])){
                return setjsons('获取报备表数据失败!');
            }
            return setjsons('抱歉！您无法查看该报备单!');
        }
        $shenhe = $this->User->getUser($item['data']['aflow']);
        $baobei = $this->User->getUser($item['data']['salesman']);
        //业务员查看订单(已完成状态)
        if(intval($item['data']['ispass']) == 3){
            $message = $this->User->getMessage($id,8);//获取对应报备单的消息通知
            if($message!=[]&&$message!=null){
                if($item['data']['salesman']==$res['data']['userId']){
                    $this->User->deleteMessageOrder($id);//已完成的订单
                }
            }
        }
        unset($item['data']['salesman']);
        unset($item['data']['aflow']);
        unset($item['data']['conmentIds']);
        return json([
            'code'=>1,
            'msg'=>'请求成功',
            'data'=>$item['data'],
            'shenhe'=>$shenhe['name'],
            'baobei'=>$baobei['name'],
            'isshenhe'=>$item['isshenhe']
        ]);
    }

    /**-------------------------------------------排期确认--------------------------------------------- */

    /**
     * 获取当前用户的排期单列表
     *
     * @return void
     */
    public function getPaiqiList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $page = intval($req->param('page'));//页码
        if($page<=0) $page = 1;
        $colName = $req->param('colName','');//客户名称（项目名称）
        $colName = preg_replace('/\s+/', '', $colName);
        $startTime = $req->param('startTime','');//开始时间
        $endTime = $req->param('endTime','');//结束时间
        $ispass = intval($req->param('ispass'));//table状态 1：全部   2：待确认    3：已确认，默认：1,建议查询条件 like
        $size = intval($req->param('size'));//每页数量
        if($size<=0) $size = 10;
        switch($ispass){
            case 1:
                $ispass = '';
                break;
            case 2:
                $ispass = 1;
                break;
            case 3:
                $ispass = 3;
                break;
            default:
                $ispass = '';
        }
        $user = $res['data'];
        $user['level']==1 ? $level = 1 : $level = 0 ;
        if($level){//管理员
            $result = $this->User->getPaiqiList($page,$colName,$startTime,$endTime,$ispass,$size);//获取全部列表
            $totalPage = $this->User->getPaiqiCount($colName,$startTime,$endTime,$ispass);//获取总数
            $pageCount = ceil($totalPage/$size);//总页数
            $data = array(
                'data'=>$result,           //数据列表
                'nowPage'=>$page,       //当前页
                'pageCount'=>$pageCount,//总页数
                'totalPage'=>$totalPage, //数据量
                'size'=>$size         //每页数量
            );
        }else{//业务员&个人
            $result = $this->User->getselfPaiqiList($user['userId'],$page,$colName,$startTime,$endTime,$ispass,$size);//获取全部列表
            $totalPage = $this->User->getselfPaiqiCount($user['userId'],$colName,$startTime,$endTime,$ispass);//获取总数
            $pageCount = ceil($totalPage/$size);//总页数
            $data = array(
                'data'=>$result,           //数据列表
                'nowPage'=>$page,       //当前页
                'pageCount'=>$pageCount,//总页数
                'totalPage'=>$totalPage, //数据量
                'size'=>$size         //每页数量
            );
        }
        return json(['code'=>1,'msg'=>'请求成功!','data'=>$data]);
    }

    /**
     * 获取排期单详情
     * @return void
     */
    public function getPaiqiInfo(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $user = $res['data'];
        $id = $req->param('id');//订单ID
        if($id==''||$id==null||!isset($id)){
            return setjsons('请选择要查看的订单!');
        }
        $pq = $this->User->getPaiqi($id);//获取排期单信息 find()
        if($pq==null||$pq==''){
            return setjsons('抱歉！未查询到订单数据');
        }
        unset($pq['bId']);
        unset($pq['userId']);
        unset($pq['openId']);
        return json(['code'=>1,'msg'=>'请求成功!','data'=>$pq]);
    }

    //DOTO 数据导出（跟列表“能看到的”一样）


    /**-------------------------------------发票管理-------------------------------------------------- */

    /**
     * 获取发票列表
     */
    public function getInvoiceList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $page = intval($req->param('page'));        //页码
        if($page==0) $page = 1;
        $size = intval($req->param('size'));//每页数量
        if($size<=0) $size = 10;
        $cusName = $req->param('cusName');          //客户名称（抬头）
        $cusName = preg_replace('/\s+/', '', $cusName);
        $level = intval($res['data']['level']);
        $taxNumber = $req->param('taxNumber','');   //发票单号
        $startTime = $req->param('startTime','');   //开始时间
        $endTime = $req->param('endTime','');       //结束时间
        $invoiceName = $req->param('invoiceName','');//发票名称
        $name = $req->param('name',''); //业务员
        $data = [];
        if($level==1 || $level==2){
            $result = $this->User->getInvoiceList($page,$cusName,$size,$taxNumber,$startTime,$endTime,$invoiceName,$name);    //所有发票
            $totalPage = $this->User->getInvoiceCount($cusName,$taxNumber,$startTime,$endTime,$invoiceName,$name);    //发票总数
            $pageCount = ceil($totalPage/$size);//总页数
            $data = array(
                'data'=>$result,        //数据列表
                'nowPage'=>$page,       //当前页
                'pageCount'=>$pageCount,//总页数
                'totalPage'=>$totalPage //数据量
            );
        }else if($level==3 || $level==4){
            $userId = $res['data']['userId'];
            $result = $this->User->getMyInvoiceList($page,$userId,$cusName,$size,$taxNumber,$startTime,$endTime);    //该用户的所有发票
            $totalPage = $this->User->getMyInvoiceCount($userId,$cusName,$taxNumber,$startTime,$endTime);    //该用户的发票总数
            $pageCount = ceil($totalPage/$size);//总页数
            $data = array(
                'data'=>$result,        //数据列表
                'nowPage'=>$page,       //当前页
                'pageCount'=>$pageCount,//总页数
                'totalPage'=>$totalPage //数据量
            );
        }
        return json(['code'=>1,'msg'=>'操作成功!','data'=>$data]);
    }

    /**
     * 发票详情
     */
    public function findInvoice(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $id = $req->param('id');    //发票id
        if(!isset($id)||$id==''){
            return setjsons('获取详情失败！');
        }
        $data = $this->User->findInvoice($id);//获取对应发票所有数据 & 业务员昵称 & 关联的订单id,项目名称,类型
        $isupdate = false;//是否可以操作发票收款金额(是否展示收款按钮)
        $isexamine = false;//是否可以审核
        if($res['data']['level']==1 || $res['data']['iscw']==1){
            if($data['isover']==3 ){
                $isupdate = true;
            }else{
                $isexamine = true;
            }
        }
        return json(['code'=>1,'msg'=>'操作成功!','data'=>$data,'isupdate'=>$isupdate,'isexamine'=>$isexamine]);
    }

    /**
     * 获取对应发票的收款金额
     */
    public function getInvoicePrice(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $id = $req->param('id');    //发票id
        if(!isset($id)||$id==''){
            return setjsons('获取详情失败！');
        }
        $result = $this->User->getInvoicePrice($id);//获取发票收款详情
        return json(['code'=>1,'msg'=>'操作成功','data'=>$result]);
    }

    /** 
     * 新增接口
     * 发票确认（确认按钮）
     */
    public function confirm(Request $request){
        $res = parent::valiToken($request->param('token'));
        if($res['code']==-1) return json($res);
        $id = $request->param('id');    //发票id
        if(!isset($id)||$id==''){
            return setjsons('获取详情失败！');
        }
        $invoiceNumber = $request->param('invoiceNumber');                   //发票号码
        $invoiceTime = $request->param('invoiceTime');                       //开票日期
        $invoiceCash = doubleval($request->param('invoiceCash',0));          //发票金额
        $invoice = $this->User->getInvoice($id);  //发票详情
        if($invoice['isover']==3){
            return setjsons('抱歉！该发票已被确认');
        }else if($res['data']['level']!=1){
            if($res['data']['iscw']!=1){
                return setjsons('抱歉！您没有该操作权限!');
            }
        }else if(!isset($invoiceNumber)||$invoiceNumber==''||$invoiceNumber==null){
            return setjsons('请填写发票号码!');
        }else if(!isset($invoiceTime)||$invoiceTime==''||$invoiceTime==null){
            return setjsons('请填写开票日期!');
        }
        $arr = array(
            'invoiceNumber' => $invoiceNumber,
            'invoiceTime' => $invoiceTime,
            'invoiceCash'=>$invoiceCash,
            'isover' => 3
        );
        $this->User->updateInvoice($id,$arr);
        $text = '您申请的发票《'.$invoice['cusName'].'》已被财务确认!';
        $index = new \app\index\controller\Index();
        $index->sendMessage($text,$invoice['salesmanId']);//消息推送
        return json(['code'=>1,'msg'=>'确认成功!']);
    }

    /**
     * 新增接口
     * 发票驳回（驳回按钮）
     * 逻辑：将对应发票关联的订单查出来，根据类型 修改掉对应订单的状态，isover改为3
     */
    public function reject(Request $request){
        $res = parent::valiToken($request->param('token'));
        if($res['code']==-1) return json($res);
        $id = $request->param('id');    //发票id
        if(!isset($id)||$id==''){
            return setjsons('获取详情失败！');
        }
        $invoice = $this->User->getInvoice($id);  //发票详情
        if($invoice['isover']==3){
            return setjsons('抱歉！该发票已被确认');
        }else if($res['data']['level']!=1){
            if($res['data']['iscw']!=1){
                return setjsons('抱歉！您没有该操作权限!');
            }
        }
        $orderIds = explode(',',$invoice['orderIds']);//关联的订单|array
        $sqlName = $this->getInvoiceType( $invoice['orderType']);//关联的订单类型|sqlName
        foreach($orderIds as $k){
            $this->User->updateOrderType($k,$sqlName,3);//修改订单类型
        }
        $this->User->deleteInvoice($id);
        $text = '您申请的发票《'.$invoice['cusName'].'》已被驳回!';
        $index = new \app\index\controller\Index();
        $index->sendMessage($text,$invoice['salesmanId']);//消息推送
        return json(['code'=>1,'msg'=>'驳回成功!']);
    }

    /**
     * 新增收款记录（最高权限操作）
     */
    public function addInvoicePrice(Request $request){
        $res = parent::valiToken($request->param('token'));
        if($res['code']==-1) return json($res);
        $id = $request->param('id');    //发票id
        $getMoneyTime = $request->param('getMoneyTime','');    //收款时间
        if(!isset($getMoneyTime)||$getMoneyTime==''){
            return setjsons('请输入收款时间！');
        }
        if(!isset($id)||$id==''){
            return setjsons('获取详情失败！');
        }
        if($res['data']['level']!=1){
            if($res['data']['iscw']!=1){
                return setjsons('抱歉！您没有该操作权限!');
            }
        }
        $accepted = $request->param('accepted');//收款金额
        if(!isset($accepted)||$accepted==''){
            return setjsons('收款金额不能为空');
        }
        $accepted = floatval($accepted);
        $invoice = $this->User->getInvoice($id);//发票详情
        if($invoice['isover']!=3){
            return setjsons('抱歉！该发票未经财务确认!');
        }
        $prize = floatval($invoice['total']) - floatval($invoice['accepted']);//未收金额
        if($accepted>$prize){
            return setjsons('金额不得大于未收总额!');
        }
        /**订单状态 */
        if($accepted==$prize){
            $sqlName = $this->getInvoiceType($invoice['orderType']);//获取订单表名
            $orderData = explode(',',$invoice['orderIds']);
            $res = $this->updateOrderType($orderData,$sqlName,5);
            if(!$res){
                return setjsons('关联订单状态更改失败！');
            }
        }
        /**发票详情 */
        $pdata = array( //新增的发票收款金额数据
            'inId'=>$id,
            'accepted'=>$accepted,
            'createTime'=>$getMoneyTime
        );
        $this->User->addInvoicePrice($pdata);//发票 收款详情新增数据
        /**发票金额 */
        $yskje = floatval($invoice['accepted'])+$accepted;//已收款金额
        $idata = array( //修改对应发票金额
            'accepted'=>$yskje
        );
        $this->User->updateInvoice($id,$idata);//修改发票数据
        return json(['code'=>1,'msg'=>'操作成功!']);
    }

    /**
     * 根据不同的订单类型返回对应表名
     * @param $type     订单类型
     */
    protected function getInvoiceType($type = 1){
        switch($type){
            case 1:
                $sqlName = 'tp_ding_ad';            //广告类
            break;
            case 2:
                $sqlName = 'tp_ding_operation';    //运营类
            break;
            case 3:
                $sqlName = 'tp_ding_ad';            //暂无
            break;
            case 4:
                $sqlName = 'tp_ding_develop';       //开发类订单
            break;
            case 5:
                $sqlName = 'tp_ding_daili';         //代理广告类
            break;
            case 6:
                $sqlName = 'tp_ding_activity';      //活动类订单
            break;
            case 7:
                $sqlName = 'tp_ding_train';         //13206订单
            break;
        }
        return $sqlName;
    }

    /**----------------------------------------------------------------数据导出------------------------------------------- */

    /**
     * (订单)
     * 下载对应的EXCEL数据
     * @see \app\index\controller\Base::Index()
     */
    public function download_Order_Excel(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $type = intval($req->param('type'));//例 自有广告类
        $id = $req->param('id');//订单id
        $colName = $req->param('colName','');//客户名称
        $name = $req->param('name','');//业务员（业务员userID）
        $startTime = $req->param('startTime','');//创建时间(开始时间)
        $endTime = $req->param('endTime','');//创建时间(结束时间)
        if($type<1||$type==3||$type>7){
            return setjsons('请选择要导出的订单类型!');
        }else if(strtotime($startTime) > strtotime($endTime)){
            return setjsons('创建时间筛选错误!');
        }
        $data = $this->getListName($type,$id,$colName,$name,$startTime,$endTime);
        $this->downloadExcel($data['list'], $data['listName'],$data['listNamein'],$data['name']);
    }
    
    /**
     * 通过订单类型，获取订单参数中文名称，字段
     * @param 订单类型 $type
     * @param 订单号 $id
     * @param 客户名称 $colName
     * @param 业务员 $name
     * @param 开始时间 $startTime
     * @param 结束时间 $endTime
     */
    private function getListName($type,$id,$colName,$name,$startTime,$endTime){
        $listName = [];
        $listNamein = [];
        $list = [];
        $name = '';
        switch ($type){
            case 1://自有类产品  发票号码：pid
                $listName = ['订单号','创建日期','客户名称','业务员','数据库类型','单价','发送时间','数量','测试项目','备注','金额',
                '发票号码','发票抬头','开票金额','开票日期','收款金额','收款日期','订单状态'];
                $listNamein = ['id','createTime','colName','name','databaseType','prize','sendTime','num','test','remark',
                    'total','pid','cusName','invoiceCash','invoiceTime','accepted','skrq','isover'];
                $list = $this->User->getzyExcel($id,$colName,$name,$startTime,$endTime);
                $name = '自有广告类';
                break;
            case 2://运营类
                $listName = ['订单号','日期','客户名称','业务员','单价','金额','开始执行日期','结束日期','备注',
                '发票号码','发票抬头','开票金额','开票日期','收款金额','收款日期','订单状态'];
                $listNamein = ['id','createTime','colName','name','prize','total','startTime','endTime','remark',
                    'pid','cusName','invoiceCash','invoiceTime','accepted','skrq','isover'];
                $list = $this->User->getyyExcel($id,$colName,$name,$startTime,$endTime);
                $name = '内容（运营）类';
                break;
            case 3://无
                break;
            case 4://开发类订单
                $listName = ['订单号','日期','客户名称','业务员','金额(总价格)','项目服务','合同服务内容（否：存在内容）','开始合作日期',
                '结束合作日期','设计完成时间','游戏/活动上线时间','备注','成本','发票号码','发票抬头','开票金额','开票日期','收款金额','收款日期','订单状态'];
                $listNamein = ['id','createTime','colName','name','total','serviceItem','text','cooTimeStart','cooTimeEnd',
                    'psTime','gameTime','remark','cost','pid','cusName','invoiceCash','accepted','skrq','invoiceTime','isover'];
                $list = $this->User->getkfExcel($id,$colName,$name,$startTime,$endTime);
                $name = '开发类';
                break;
            case 5://代理广告类订单
                $listName = ['订单号','日期','客户名称','业务员','产品类型','金额','开始执行日期','结束执行日期','执行内容','成本',
                '发票号码','发票抬头','开票金额','开票日期','收款金额','收款日期','订单状态'];
                $listNamein = ['id','createTime','colName','name','proType','total','startTime','endTime','content','cost',
                    'pid','cusName','invoiceCash','invoiceTime','accepted','skrq','isover'];
                $list = $this->User->getdlExcel($id,$colName,$name,$startTime,$endTime);
                $name = '代理广告类';
                break;
            case 6://活动类订单
                $listName = ['订单号','日期','客户名称','业务员','金额(总金额)','单价（元）','开始日期',
                '结束日期','执行内容','备注','成本','发票号码','发票抬头','开票金额','开票日期','收款金额','收款日期','订单状态'];
                $listNamein = ['id','createTime','colName','name','total','prize','startTime','endTime','context',
                    'remark','cost','pid','cusName','invoiceCash','invoiceTime','accepted','skrq','isover'];
                $list = $this->User->gethdExcel($id,$colName,$name,$startTime,$endTime);
                $name = '活动类';
                break;
            case 7://12306订单
                $listName = ['订单号','日期','客户名称','业务员','金额(总金额)','单价（元）','发送时间',
                '数量（条）','联系人','联系方式','备注','成本','发票号码','发票抬头','开票金额','开票日期','收款金额','收款日期','订单状态'];
                $listNamein = ['id','createTime','colName','name','total','prize','sendTime','num','linkman','comNumber',
                    'remark','cost','pid','cusName','invoiceCash','invoiceTime','accepted','skrq','isover'];
                $list = $this->User->gettrExcel($id,$colName,$name,$startTime,$endTime);
                $name = '12306订单';
                break;
        }
        return array('listName'=>$listName,'listNamein'=>$listNamein,'list'=>$list,'name'=>$name);
    }
    
    /*
        *导出EXCEL
        *@param $list|array        数据                                                    例:[{name:'zjl',age:18},{name:'ldh',age:20}]
        *@param $listName|array    字段名称                                            例:['昵称','头像']
        *@param $listNamein|array  字段名(English)       例：['name','pic']
        */
    private function downloadExcel($list,$listName,$listNamein,$ename){
        //1.从数据库中取出数据
        //2.加载PHPExcle类库
        vendor('phpoffice.phpexcel.Classes.PHPExcel');
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //设置A列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //5.设置表格头（即excel表格的第一行）
        foreach ($listName as $k=>$v){
            $zimu = $this->getZimu($k+1);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($zimu.'1', $listName[$k]);
            $objPHPExcel->getActiveSheet()->getStyle($zimu.'1')->getFont()->setBold(true);
            //设置单元格宽度
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($zimu)->setWidth(20);
        }
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for ($i=0;$i<count($list);$i++){
            foreach ($listName as $k=>$v){
                $objPHPExcel->getActiveSheet()->setCellValue($this->getZimu($k+1).($i+2),$list[$i][$listNamein[$k]]);
                $objPHPExcel->getActiveSheet()->getStyle($this->getZimu($k+1).($i+2))->getAlignment()->setWrapText(true);
            }
        }
//         $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(40);
        //7.设置保存的Excel表格名称
        $filename = 'zx'.$ename.'-'.time().'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('sheet1');
        //9.设置浏览器窗口下载表格
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * 长度换字母位置
     * @param unknown $count
     */
    private function getZimu($count = 1){
        $z = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $z[$count-1];
    }

}