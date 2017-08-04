<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Controller;
use Think\Controller;

/**
 * 控制器基类
 * 所有控制器都要继承他
 * @author lihao 修改<lh@tiptime.com>
*/
class BaseController extends Controller{
    protected   function _initialize(){
        /* 读取数据库中的配置 */
        $config = S('DB_CONFIG_DATA');
        if(!$config){
            $config = api('Config/lists');
        }
        C($config); //添加配置
    }

    protected function  J_J($status,$data,$msg){
        $datas['status'] = $status;
        $datas['data'] = $data;
        $datas['msg'] = $msg;
        $this->ajaxReturn($datas);
    }

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function checkRule($rule, $type=1, $mode='url'){
        if(defined("IS_ROOT") && IS_ROOT || is_administrator()){
            return true;//管理员允许访问任何页面
        }
        static $Auth    =   null;
        if (!$Auth) {
            $Auth       =   new \Think\Auth();
        }
        if(!$Auth->check($rule,UID,$type,$mode)){
            return false;
        }
        return true;
    }


}
