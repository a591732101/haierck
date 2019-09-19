<?php

/**
 * User: linyangting
 * Date: 2019/8/26
 */
namespace app\games\model\g201909\sdxxsj;

use think\Db;

class User extends \think\Model
{
    
    public function insertUserZhu($arr){
        return Db::table("tp_201909_sdinfo_zhu")->insert();
    }
    
    public function insertUseryuan($arr){
        return Db::table("tp_201909_sdinfo_yuan")->insert();
    }
    
}