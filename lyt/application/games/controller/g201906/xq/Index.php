<?php
/**
 * Author: linyangting
 * Date: 2019/6/12
 */

namespace app\games\controller\g201906\xq;
    
use app\games\model\g201906\xq\User;
use think\Controller;
use think\Request;

class Index extends Controller
{
    public $User;  
    
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
     * 首页
     */
    public function Index(Request $request){
        return $this->fetch();
    }
    
    public function user(){
        return $this->fetch();
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
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'id')
        ->setCellValue('B1', '微信昵称')
        ->setCellValue('C1', '用户昵称')
        ->setCellValue('D1', '用户电话')
        ->setCellValue('E1', '正确率')
        ->setCellValue('F1', '时长')
        ->setCellValue('G1', '排名')
        ->setCellValue('H1', '创建时间');
        //设置A列水平居中
        // $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
        // ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(20);
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($list);$i++){
            $ranks = $list[$i]['rank'];
            // $nickName = $list[$i]['nickName'];
            // $tel = $list[$i]['tel'];
            // if($nickName==null) $nickName='';
            // if($tel==null) $tel='';
            if($list[$i]['acc']==0&&$list[$i]['duration']==100){
                $ranks = '未进入排名';
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$list[$i]['id']);//id
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),self::filterEmoji($list[$i]['wxName']));//微信昵称
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$list[$i]['nickName']);//用户昵称
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$list[$i]['tel']);//用户电话
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$list[$i]['acc'].'%');//正确率
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$list[$i]['duration'].'s');//时长
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$ranks);//排名
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$list[$i]['createTime']);//创建时间
        }
        //7.设置保存的Excel表格名称
        $filename = '东莞时代-猜歌答题'.date('H:i:s').'.xls';
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
    
    public function hello(){
        $arr = array(15,84,2,415,421,5);
        for($j=0;$j<count($arr);$j++){
            sleep($arr[$j]/1000);
            
        }
    }



}