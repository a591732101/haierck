<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\index\model\User;


class Index extends Controller
{
    private $User;
    
    function __construct(Request $request = null)
    {
        // header("Access-Control-Allow-Origin: *");
        // header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        // header("Content-type: text/html; charset=utf-8");
        if(strtoupper($_SERVER['REQUEST_METHOD'])== 'OPTIONS'){
            return;
        }
        parent::__construct($request);
        $this->User = new User();
    }
    
    /**
     * 获取用户数据
     */
    public function getU(Request $request){
        $page = intval($request->param('page'));
        if($page==0){
            $page = 1;
        }
        $res = $this->User->getUInfoss($page);//获取基本数据
        $count = $this->User->getUInfoCountss();//基本数据总数
        $pageCount = ceil($count/15);//总页数，每页15条数据
        return json(['code'=>1,'data'=>$res,'co'=>$count,'pageCount'=>$pageCount,'pageSize'=>15,'pageNow'=>$page]);
    }
    
    /*
     *导出EXCEL 
     */
    public function downloadExcel(){
        //1.从数据库中取出数据
        $list = $this->User->getExcelgetExcelss();
        //2.加载PHPExcle类库
//         vendor('PHPExcel');
        vendor('phpoffice.phpexcel.Classes.PHPExcel');
//         vendor('phpoffice.phpexcel.Classes.PHPExcel.Worksheet.Drawing');
//         vendor('phpoffice.phpexcel.Classes.PHPExcel.Writer.Excel2007');
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //设置A列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
//         ->setCellValue('A1', '微信昵称')
        ->setCellValue('B1', '昵称')
        ->setCellValue('C1', '性别')
        ->setCellValue('D1', '年龄')
        ->setCellValue('E1', '公司名称')
        ->setCellValue('F1', '职务')
        ->setCellValue('G1', '电话')
        ->setCellValue('H1', '微信号')
        ->setCellValue('I1', '爱好')
        ->setCellValue('J1', '心中TA的样子')
        ->setCellValue('K1', '个人生活照');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); //字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); //字体加粗
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(5);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(20);
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($list);$i++){
            $sex = $list[$i]['sex'];
            switch ($sex){
                case 1:
                    $sex = '男';
                    break;
                case 2:
                    $sex = '女';
                    break;
            }
//             $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$list[$i]['wxName']);//微信昵称
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$list[$i]['nickName']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$sex);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$list[$i]['age']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$list[$i]['company']);
            $objPHPExcel->getActiveSheet()->getStyle('E'.($i+2))->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$list[$i]['position']);
            $objPHPExcel->getActiveSheet()->getStyle('F'.($i+2))->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$list[$i]['tel']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$list[$i]['wxNumber']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),$list[$i]['likes']);
            $objPHPExcel->getActiveSheet()->getStyle('I'.($i+2))->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),$list[$i]['expect']);
            $objPHPExcel->getActiveSheet()->getStyle('J'.($i+2))->getAlignment()->setWrapText(true);
            
            $pic = substr($list[$i]['picUrl'], 36);
            // 图片生成
            $objDrawing[$i+2] = new \PHPExcel_Worksheet_Drawing();
            $objDrawing[$i+2]->setPath('./wxPic/xq/'.$pic);
            // 设置宽度高度
            $objDrawing[$i+2]->setHeight(40);//照片高度
//             $objDrawing[$i]->setWidth(80); //照片宽度
            /*设置图片要插入的单元格*/
            $objDrawing[$i+2]->setCoordinates('K'.($i+2));
            // 图片偏移距离
            $objDrawing[$i+2]->setOffsetX(12);
            $objDrawing[$i+2]->setOffsetY(12);
            $objDrawing[$i+2]->setWorksheet($objPHPExcel->getActiveSheet());
        }
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(40);
        //7.设置保存的Excel表格名称
        $filename = 'XQ'.date('H:i:s').'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('sheet1');
        //9.设置浏览器窗口下载表格
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
     * 过滤微信昵称中特殊符号
     * @param $emojiStr
     */
    public function filterEmoji($emojiStr){
        $emojiStr = preg_replace_callback('/./u',function(array $match){
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },$emojiStr);
        return $emojiStr;
    }
    
    public function Index(){
        return $this->fetch();
    }
    
    public function U(Request $request){
        $page = intval($request->param('page'));
        if($page==0){
            $page = 1;
        }
        $res = $this->User->getUss($page);//获取基本数据
        $count = $this->User->getUtss();//基本数据总数
        $pageCount = ceil($count/15);//总页数，每页15条数据
        return json(['code'=>1,'data'=>$res,'co'=>$count,'pageCount'=>$pageCount,'pageSize'=>15,'pageNow'=>$page]);
    }
    
    public function user(){
        return $this->fetch();
    }
    
    public function a($a = 0){
        if($a>1){
            throw new \Exception('错误的对象!', 500);
        }else{
            return true;
        }
    }
    
    public function he(){
        $a = 3;
        try {
            self::a($a);
        }catch (\Exception $e){
            var_dump($e->getMessage());
        }
    }
    
    public function sql(){
        $start = '';
        $end = '2019-6-19';
        $res = $this->User->gett($start,$end);
        return json($res);
    }
    
    public function ar(){
        $ordrtoi = '?-123456-?-456789-?-?';//订单流程
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
                $name = self::geta($v);
                array_push($arr,array(
                    'index'=>$k,
                    'isUpdate'=>1,
                    'name'=>$name
                ));
                continue;
            }
        }
        return json($arr);
    }
    
    public  function geta($id){
        switch (intval($id)){
            case 123456:
                $name = '周杰伦';
                break;
            case 456789:
                $name = 'ldsa';
                break;
            default:
                $name = null;
        }
        return $name;
    }
    
    public function getMillisecond() {//获取毫秒级时间戳
        list($usec, $sec) = explode(" ", microtime());
        $usec = str_replace('.', '', $usec);
        $ctime=substr($sec.$usec,0,17);
        return time()*1000;
    }
    
    public function dinglogin(){
        $url = 'https://oapi.dingtalk.com/sns/getuserinfo_bycode';
        // $timestamp = $this->getMillisecond();
        $timestamp = time()*1000;
        $accessKey = 'dingoahfiy99t8di6eatc8';
        $signature = $this->getsignature($timestamp);
        $arr = array(
            'accessKey' => $accessKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'code' => 'fdsafsdfi32sdsytf'
        );
        $res = $this->post_curls($url,$arr);
        return $res;
    }
    
    protected function getsignature($timestamp){
        // 根据timestamp, appSecret计算签名值
        $s = hash_hmac('sha256', $timestamp, 'u0kqaUqQpI3LqV04hafpEjPHzc5osVGAlEgpexHT494iVZDeR8kMspluOlnl5RTg', true);
        $signature = base64_encode($s);
        $urlencode_signature = urlencode($signature);
        return $urlencode_signature;
    }
    
    /**
     * POST请求https接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $post [请求的参数]
     * @return  string
     */
    protected function post_curls($url, $post_data = array()){
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $o = "";
        foreach ( $post_data as $k => $v ){
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        return $post_data;
    }
    
    protected $appid = 'dingoahfiy99t8di6eatc8';
    // protected $appkey = "dingpco7qlouse3bseul";
    private $appsecret = "u0kqaUqQpI3LqV04hafpEjPHzc5osVGAlEgpexHT494iVZDeR8kMspluOlnl5RTg";  //秘钥(扫码登录的)
    
    public function getdingToken(){
        $url = 'https://oapi.dingtalk.com/sns/gettoken';
        $arr = ['appid'=>$this->appid,'appsecret'=>$this->appsecret];
        $res = $this->get_Post_Request($url,$arr);
        $res = json_decode($res,true);
        if($res['errcode']==0){
            return $res['access_token'];
        }
        return null;
    }
    
    /**
     * get请求
     */
    protected function get_Post_Request($url = null, $paramArr = []){
        $param = '';
        foreach ($paramArr as $k => $v){
            $param.="$k=$v&";
        }
        $curlPost = substr($param, 0,strlen($param)-1);
        $url =$url.'?'.$curlPost;//url拼接
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//设置这个才能拿到token
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        curl_close($ch);
        return  $output;
    }
    
    public function asd(){
        header("Content-type: text/html; charset=utf-8");
        $errmsg = '提示弹出来了';
        echo "<script> alert(\"{$errmsg}\"); </script>";
        $url = 'https://aiwei.weilot.com/ding/admin/login';
        echo "<meta http-equiv='Refresh' content=4; URL=$url>";
//         header("location:$url");
        exit;
        $token = $this->getdingToken();
        $url = 'https://oapi.dingtalk.com/sns/get_persistent_code?access_token='.$token;
        $data = array(
            'tmp_auth_code' => '534dace087eb399ba937be076bde4c7e'
        );
        $data = json_encode($data);
        $res = $this->http_post_json($url, $data);
        return json(json_decode($res,true));
    }
    
    protected function http_post_json($url, $jsonStr){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//不验证证书
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }
    
    public function w(){
         $a = array('id'=>1,'msg'=>123);
         $b = array('id'=>2,'msg'=>123);
         $c = array('id'=>2,'msg'=>1233);
         $d = array('id'=>3,'msg'=>145);
         $e = array('id'=>5,'msg'=>123);
        $arr = array();
        $arr = array_merge($a,$b,$c,$d,$e);
//         $arr = array_unique($arr, SORT_REGULAR);
        var_dump($arr);
        return json($arr);
    }
    
    public function y(){
        $a = [['a'=>1,'gfds'=>123],['a'=>1,'gfds'=>123]];
        $b = [['a'=>2,'gfds'=>5432]];
        $c = [['a'=>3,'dfsf']];
        $d = [['a'=>4]];
        $e = [['a'=>1]];
        $arr = [];
        $arr = array_merge($arr,$a,$b,$c,$d,$e);
        return json($arr);
    }
    
    public function qwe(Request $req){
        $w = intval(date('w'));
        var_dump($w);
    }
    
    /**
     * 消息推送
     * @param text          消息文本
     * @param userid_list   用户userid列表
     * @param dept_id_list  部门id列表
     * @param to_all_user   是否发送给全公司
     * @return #true:发送成功  false：发送失败
     */
    public function sendMessage($tex,$userid_list){
        if(!isset($tex) || $tex=='') return false;
        $url = 'https://oapi.dingtalk.com/topapi/message/corpconversation/asyncsend_v2';
        // $msg = array(
        //     'msgtype'=>'text',
        //     'text'=>array(
        //         'content'=>'【钉钉-众行订单应用】'.$tex.'(点击头像可进入应用)'
        //     )
        // );
        $msg = array(
            'msgtype'=>'link',
            'link'=>array(
                'messageUrl'=>'eapp://pages/index/index',
                'picUrl'=>'https://aiwei.weilot.com/static/ding/tongzhi/8.png',//链接图片地址
                'title'=>'【众行-订单应用】',
                'text'=>$tex
            )
        );
        $msg = json_encode($msg);
        $token = $this->getToken();
        $data = array(
            'access_token' => $token,
            'agent_id' => 237454020,
            'userid_list' => $userid_list,   //用户userid列表
            'msg'=> $msg
        );
        $res = $this->post_curls($url,$data);
        $errcode = json_decode($res,true)['errcode'];
        if($errcode==0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * id,openId,week,createTime
     */
    public function sign(){
        $openId = '123456789';
        $date = date('Y-m-d');
        $week = intval(date('w'));
        !$week?$week=7:false;
        $res = $this->User->getWeekSignCount($openId,$date);
        if(!$res){
            $arr  = array(
                'openId'=>$openId,
                'week'=>$week,
                'createTime'=>$date
            );
            $this->User->addsign($arr);
            return json(['code'=>1,'msg'=>'签到成功']);
        }
        return json(['code'=>2,'msg'=>'您今天已经签到过了']);
    }
    
    /**
     * 周签信息
     */
    public function weekSign(){
        $w = intval(date('w'));
        !$w?$w=7:false;
        $a = self::aa($w);
        $d = $w-1;
        $date = date('Y-m-d',strtotime("-$d day"));//本周开始时间
        $openId = '123456789';
        $sign = $this->User->getWeekSignList($openId,$date);//获取本周内，用户签到的数据,select（建议SQL中存在签到的星期数）
        return json(['msg'=>"today is $a",'weekSignCount'=>count($sign),'data'=>$sign]);
    }
    
    
    
    
    function aa($a){
        switch ($a){
            case 1:
                $b = '周一';
                break;
            case 2:
                $b = '周二';
                break;
            case 3:
                $b = '周三';
                break;
            case 4:
                $b = '周四';
                break;
            case 5:
                $b = '周五';
                break;
            case 6:
                $b = '周六';
                break;
            case 7:
                $b = '周日';
                break;
            default:
                $b = '未知';
        }
        return $b;
    }
    
    /**
     * 
     */
    public function demo(){
        
    }
    
}
