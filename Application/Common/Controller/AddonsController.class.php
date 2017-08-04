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
use Common\Controller\BaseController;

/**
 * 插件控制器
 * @author lihao <lh@tiptime.com>
 */
class AddonsController extends BaseController{
     protected function checkAuth(){
         /**
          * 需要权限检测,这里只检测是否有插件后台的权限，因为
          * 插件的调度是忽略模块控制器方法的，如果采取常规的
          * 模块控制器检测，则没什么意义，插件的访问控制也是相对
          * 简单的
          */
         $rule  = strtolower('Admin/AddonsBack/adminList');
         if (!$this->checkRule($rule,array('in','1,2')) ){
             $this->error('未授权访问!');
         }
     }
}
