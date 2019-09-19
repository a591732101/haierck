<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\index\model\User;


class Base extends Controller
{
    private $User;
    protected $a=0;
    
    function __construct(Request $request = null)
    {
        // header("Access-Control-Allow-Origin: *");
        // header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        // header("Content-type: text/html; charset=utf-8");
//         if(strtoupper($_SERVER['REQUEST_METHOD'])== 'OPTIONS'){
//             return;
//         }
        parent::__construct($request);
        $this->User = new User();
    }
    
    
    public function Index(Request $request){
        $this->a = 1;
    }
    
    /**
     * 根据不同的订单类型返回对应表名
     * @param $type     订单类型
     */
    public function getInvoiceType($type = 1){
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
    
}
