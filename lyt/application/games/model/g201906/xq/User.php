<?php

/**
 * User: linyangting
 * Date: 2019/6/12
 */
namespace app\games\model\g201906\xq;

use think\Db;

class User extends \think\Model
{

    /**
     * 数据列表
     */
    public function getUInfoss($page){
        $page = 15*($page-1);
        return Db::table("tp_201906_xq_user")->where('nickName','<>','')->limit($page,15)->select();
    }
    
    /**
     * 数据总数
     */
    public function getUInfoCountss(){
        return Db::table("tp_201906_xq_user")->where('nickName','<>','')->count();
    }
    
    public function getExcelgetExcelss(){
        return Db::table("tp_201906_xq_user")->where('nickName','<>','')->select();
    }
    
}