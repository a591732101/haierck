<?php
namespace app\index\controller;

use think\Request;
use app\index\model\User;

class A extends Base
{
    public $User;
    public $a;
    
    
    public function fd(){
        $a = $this->a->get('j');
        var_dump($a);
    }
    
    
    public function __construct(Request $request = null){
        parent::__construct($request);
        $this->User = new User();
    }
    
    /**
     * 下载对应的EXCEL数据
     * @see \app\index\controller\Base::Index()
     */
    public function download_Order_Excel(Request $req){
//         $token = $req->param('token');
//         $res = parent::valiToken($token);
        
        $type = intval($req->param('type'));//例 自有广告类
        $id = $req->param('id');//订单id
        $colName = $req->param('colName','');//客户名称
        $name = $req->param('name','');//业务员（业务员userID）
        $startTime = $req->param('startTime','');//创建时间(开始时间)
        $endTime = $req->param('endTime','');//创建时间(结束时间)
        if($type>1||$type==3||$type<7){
            return setjsons('请选择要导出的订单类型!');
        }else if(strtotime($startTime) > strtotime($endTime)){
            return setjsons('创建时间筛选错误!');
        }
        $data = $this->getListName($type,$id,$colName,$name,$startTime,$endTime);
        $this->downloadExcel($data['list'], $data['listName'],$data['listNamein']);
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
        switch ($type){
            case 1://自有类产品  发票号码：pid
                $listName = ['订单号','创建日期','客户名称','业务员','数据库类型','单价','发送时间','数量','测试项目','备注','金额',
                '发票号码','发票抬头','开票金额','收款日期','收款金额'];
                $listNamein = ['id','createTime','colName','name','databaseType','prize','sendTime','num','test','remark',
                    'total','pid','cusName','total','invoiceTime','invoiceCash'];
                $list = $this->User->getzyExcel($id,$colName,$name,$startTime,$endTime);
                break;
            case 2://运营类
                $listName = ['订单号','日期','客户名称','业务员','单价','金额','开始执行日期','结束日期','备注',
                '发票号码','发票抬头','开票金额','收款日期','收款金额'];
                $listNamein = ['id','createTime','colName','name','prize','total','startTime','endTime','remark',
                    'pid','cusName','total','invoiceTime','invoiceCash'];
                $list = $this->User->getyyExcel($id,$colName,$name,$startTime,$endTime);
                break;
            case 3://无
                break;
            case 4://开发类订单
                $listName = ['订单号','日期','客户名称','业务员','金额(总价格)','项目服务','合同服务内容（否：存在内容）','开始合作日期',
                '结束合作日期','设计完成时间','游戏/活动上线时间','备注','成本','发票号码','发票抬头','开票金额','收款日期','收款金额'];
                $listNamein = ['id','createTime','colName','name','total','serviceItem','text','cooTimeStart','cooTimeEnd',
                    'psTime','gameTime','remark','cost','pid','cusName','total','invoiceTime','invoiceCash'];
                $list = $this->User->getkfExcel($id,$colName,$name,$startTime,$endTime);
                break;
            case 5://代理广告类订单
                $listName = ['订单号','日期','客户名称','业务员','产品类型','金额','开始执行日期','结束执行日期','执行内容','成本',
                '发票号码','发票抬头','开票金额','收款日期','收款金额'];
                $listNamein = ['id','createTime','colName','name','proType','total','startTime','endTime','content','cost',
                    'pid','cusName','total','invoiceTime','invoiceCash'];
                $list = $this->User->getdlExcel($id,$colName,$name,$startTime,$endTime);
                break;
            case 6://活动类订单
                $listName = ['订单号','日期','客户名称','业务员','金额(总金额)','单价（元）','开始日期',
                '结束日期','执行内容','备注','成本','发票号码','发票抬头','开票金额','收款日期','收款金额'];
                $listNamein = ['id','createTime','colName','name','total','prize','startTime','endTime','context',
                    'remark','cost','pid','cusName','total','invoiceTime','invoiceCash'];
                $list = $this->User->gethdExcel($id,$colName,$name,$startTime,$endTime);
                break;
            case 7://13206订单
                $listName = ['订单号','日期','客户名称','业务员','金额(总金额)','单价（元）','发送时间',
                '数量（条）','联系人','联系方式','备注','成本','发票号码','发票抬头','开票金额','收款日期','收款金额'];
                $listNamein = ['id','createTime','colName','name','total','prize','sendTime','num','linkman','comNumber',
                    'remark','cost','pid','cusName','total','invoiceTime','invoiceCash'];
                $list = $this->User->gettrExcel($id,$colName,$name,$startTime,$endTime);
                break;
        }
        return array('listName'=>$listName,'listNamein'=>$listNamein,'list'=>$list);
    }
    
    /*
     *导出EXCEL
     *@param $list|array        数据                                                    例:[{name:'zjl',age:18},{name:'ldh',age:20}]
     *@param $listName|array    字段名称                                            例:['昵称','头像']
     *@param $listNamein|array  字段名(English)       例：['name','pic']
     */
    private function downloadExcel($list,$listName,$listNamein){
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
        $filename = 'zx-'.time().'.xls';
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
    
    
    /**
     * 订单列表
     * @param Request $req
     */
    public function getOrderList(Request $req){
        $res = parent::valiToken($req->param('token'));
        if($res['code']==-1) return json($res);
        $page = intval($req->param('page'));//页码
        $type = intval($req->param('type'));//订单类型
        $colName = $req->param('colName');//客户名称（项目名称）
        $colName = preg_replace('/\s+/', '', $colName);
        $startTime = $req->param('startTime');//开始时间
        $endTime = $req->param('endTime');//结束时间
        $id = $req->param('id');//订单id
        //TODO 请求过滤参数
        if(!$page) $page = 1;
        if(!$type) return setjsons('请选择订单类型!');
        $level = intval($res['data']['level']);//权限
        switch($level){
            case 1://最高权限
                $res = $this->User->getAllOrderList($page,$colName,$startTime,$endTime,$type,$id);//订单列表,每页10条记录
                $totalPage = $this->User->getAllOCount($colName,$startTime,$endTime,$type,$id);    //数据总数
                $pageCount = ceil($totalPage/10);//总页数
                $data = array(
                    'data'=>$res,           //数据列表
                    'nowPage'=>$page,       //当前页
                    'count'=>$pageCount,//总页数
                    'size'=>$totalPage //数据量
                );
                break;
            case 2://管理员权限
                $department = explode(',',$res['data']['department']);//用户部门id集
                $res = $this->User->getSecOrderList($page,$colName,$startTime,$endTime,$department,$res['data']['userId'],$type,$id);//查询他所在部门的所有订单，以及跟他关联的订单
                $totalPage = $this->User->getSecOCount($colName,$startTime,$endTime,$department,$res['data']['userId'],$type,$id);    //数据总数
                $pageCount = ceil($totalPage/10);//总页数
                $data = array(
                    'data'=>$res,           //数据列表
                    'nowPage'=>$page,       //当前页
                    'count'=>$pageCount,//总页数
                    'size'=>$totalPage //数据量
                );
                break;
            default://业务员权限 & 普通权限(只能看到自己的订单，和自己相关联的订单)
                $res = $this->User->getthrOrderList($page,$colName,$startTime,$endTime,$res['data']['userId'],$type,$id);
                $totalPage = $this->User->getthrOCount($colName,$startTime,$endTime,$res['data']['userId'],$type,$id);    //数据总数
                $pageCount = ceil($totalPage/10);//总页数
                $data = array(
                    'data'=>$res,           //数据列表
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
        if($this->isLogin) return $this->loginOut();   //重新获取用户信息
        $id = $request->param('id');        //订单id
        $type = intval($request->param('type'));    //订单类型
        if(!isset($id)||$id=='') return setjsons('订单参数不能为空!');
        if(!$type) return setjsons('订单参数不能为空!');
        $res = $this->User->getOrderInfo($type,$id);//获取订单数据,通过订单类型和ID
        $user = $this->User->getUser($res['salesman']);
        $res['name'] = $user['name'];
        return json(['code'=>1,'msg'=>'操作成功','data'=>$res['data']]);
    }
    
    
    /** --------------------------------------发票------------------------------------------------- */
    
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
        $data = [];
        if($level==1 || $level==2){
            $res = $this->User->getInvoiceList($page,$cusName,$size);    //所有发票
            $totalPage = $this->User->getInvoiceCount($cusName);    //发票总数
            $pageCount = ceil($totalPage/$size);//总页数
            $data = array(
                'data'=>$res,           //数据列表
                'nowPage'=>$page,       //当前页
                'pageCount'=>$pageCount,//总页数
                'totalPage'=>$totalPage //数据量
            );
        }else if($level==3 || $level==4){
            $userId = $res['data']['userId'];
            $res = $this->User->getMyInvoiceList($page,$userId,$cusName,$size);    //该用户的所有发票
            $totalPage = $this->User->getMyInvoiceCount($userId,$cusName);    //该用户的发票总数
            $pageCount = ceil($totalPage/$size);//总页数
            $data = array(
                'data'=>$res,           //数据列表
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
        $isupdate = false;//是否可以操作发票收款金额(是否展示收款按钮)
        if($res['data']['level']==1){
            $isupdate = true;
        }
        $data = $this->User->findInvoice($id);//获取对应发票所有数据 & 业务员昵称 & 关联的订单id,项目名称,类型
        return json(['code'=>1,'msg'=>'操作成功!','data'=>$data,'isupdate'=>$isupdate]);
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
        $res = $this->User->getInvoicePrice($id);//获取发票收款详情
        return json(['code'=>1,'msg'=>'操作成功','data'=>$res]);
    }
    
    /**-------------------------------------------------------------------------------------------------------------
     * 新增接口
     * 发票确认（确认按钮）
     */
    public function confirm(Request $request){
        $res = parent::valiToken($request->param('token'));
        if($res['code']==-1) return json($res);
        $id = $request->param('id');    //发票id
        if(!isset($id)||$id==''){
            return json(['code'=>0,'msg'=>'获取详情失败！']);
        }
        $invoiceNumber = $request->param('invoiceNumber');                   //发票号码
        $invoiceTime = $request->param('invoiceTime');                       //开票日期
        $invoiceCash = doubleval($request->param('invoiceCash',0));          //发票金额
        $invoice = $this->User->getInvoice($id);  //发票详情
        if($invoice['isover']==3){
            return json(['code'=>0,'msg'=>'抱歉！该发票已被确认']);
        }else if($this->userInfo['level']!=1){
            if($this->userInfo['iscw']!=1){
                return json(['code'=>0,'msg'=>'抱歉！您没有该操作权限!']);
            }
        }else if(!isset($invoiceNumber)||$invoiceNumber==''||$invoiceNumber==null){
            return json(['code'=>0,'msg'=>'请填写发票号码!']);
        }else if(!isset($invoiceTime)||$invoiceTime==''||$invoiceTime==null){
            return json(['code'=>0,'msg'=>'请填写开票日期!']);
        }
        $arr = array(
            'invoiceNumber' => $invoiceNumber,
            'invoiceTime' => $invoiceTime,
            'invoiceCash'=>$invoiceCash,
            'isover' => 3
        );
        $this->User->updateInvoice($id,$arr);
        $text = '您申请的发票《'.$invoice['cusName'].'》已被财务确认!';
        $index = new Index();
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
            return json(['code'=>0,'msg'=>'获取详情失败！']);
        }
        $invoice = $this->User->getInvoice($id);  //发票详情
        if($invoice['isover']==3){
            return json(['code'=>0,'msg'=>'抱歉！该发票已被确认']);
        }else if($this->userInfo['level']!=1){
            if($this->userInfo['iscw']!=1){
                return json(['code'=>0,'msg'=>'抱歉！您没有该操作权限!']);
            }
        }
        $orderIds = explode(',',$invoice['orderIds']);//关联的订单|array
        $sqlName = $this->getInvoiceType( $invoice['orderType']);//关联的订单类型|sqlName
        foreach($orderIds as $k){
            $this->User->updateOrderType($k,$sqlName,3);//修改订单类型
        }
        $this->User->deleteInvoice($id);
        $text = '您申请的发票《'.$invoice['cusName'].'》已被驳回!';
        $index = new Index();
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
            return json(['code'=>0,'msg'=>'请输入收款时间！']);
        }
        if(!isset($id)||$id==''){
            return json(['code'=>0,'msg'=>'获取详情失败！']);
        }
        if($this->userInfo['level']!=1){
            if($this->userInfo['iscw']!=1){
                return json(['code'=>0,'msg'=>'抱歉！您没有该操作权限!']);
            }
        }
        $accepted = $request->param('accepted');//收款金额
        if(!isset($accepted)||$accepted==''){
            return json(['code'=>0,'msg'=>'收款金额不能为空']);
        }
        $accepted = floatval($accepted);
        $invoice = $this->User->getInvoice($id);//发票详情
        if($invoice['isover']!=3){
            return json(['code'=>0,'msg'=>'抱歉！该发票未经财务确认!']);
        }
        $prize = floatval($invoice['total']) - floatval($invoice['accepted']);//未收金额
        if($accepted>$prize){
            return json(['code'=>0,'msg'=>'金额不得大于未收总额!']);
        }
        /**订单状态 */
        if($accepted==$prize){
            $sqlName = $this->getInvoiceType($invoice['orderType']);//获取订单表名
            $orderData = explode(',',$invoice['orderIds']);
            $res = $this->updateOrderType($orderData,$sqlName,5);
            if(!$res){
                return json(['code'=>0,'msg'=>'关联订单状态更改失败！']);
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
    
    public function hello(){
        $res = $this->User->aw();
        return json([$res=='']);
    }
    
    public function gettime(){
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 100);//%.0f不保留小数
        return intval($msectime);
    }
    
    public function hh($p){
        switch ($p){
            case 1:
                $a = 123;
                break;
            case 2:
                $a = 456;
                break;
            case 3:
                $a = 789;
                break;
            case 4:
                $a = 000;
                break;
        }
        return $a;
    }
    
    public function i($a,$b){
        $c = $a+$b;
        return $c;
    }
    
    public function v(){
        $a = 1;
        $res = $this->i($a,self::hh(2));
        var_dump($res);
    }
    
    public function excel(){
        $ex = '<form action="http://game.vimionline.com/games/g201906.xq.index/downloadExcel" method="get">                
	    		<button class="btn btn-success" style="float: right;margin: 10px 23px;">导出Excel</button>
			</form>';
        echo "$ex";
    }
    
    public function op(){
        return ['code'=>1,'msg'=>'he;l'];
    }
    
    public function po(Request $req){
        var_dump($this->op());
        die;
        $a = array('nihao'=>123,'smgui'=>'hahahah');
        return rj(msg(100),1);
    }
    
    public function yy(){
        $time = '2019-07-23T16:20:00.000Z';
        
        $time = str_replace('T', ' ',$time);
        $index = stripos($time,'.');
        $index?$time=substr($time,0, $index):false;
        
        $a = date('Y-m-d H:i:s',strtotime($time));
        var_dump($a);
        
        echo debug('begin','end').'s';
        die;
        $url = url("",'','',true);
        var_dump($url);
    }
    
    public function k(){
        $a = 66;
        var_dump(strlen($a));
        echo '<br>';
        var_dump(intval(substr($a, 0,1)));
        die;
        return rj();
    }
    
    
    
}
