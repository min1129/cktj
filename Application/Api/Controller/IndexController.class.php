<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Think\Controller;


class IndexController extends ApiController{
    /**
     * api统一入口
     */
    public function index(){

        $_R = isset($_REQUEST['_R'])?$_REQUEST['_R']:'App'; //资源
        $_M = isset($_REQUEST['_M'])?$_REQUEST['_M']:'Common'; //模块
        $_C = $_REQUEST['_C']; //接口类
        $_A = $_REQUEST['_A']; //接口
        if(!empty($_C) && !empty($_A)){
            $this->unsetGetPost('_R');
            $this->unsetGetPost('_M');
            $this->unsetGetPost('_C');
            $this->unsetGetPost('_A');
            if($_R != 'App'){
                $result = api(array($_R,$_M,$_C,$_A),$_REQUEST);
            }else{
                $result = api(array($_M,$_C,$_A),$_REQUEST);
            }
            if($result === false){ //只要返回false时认为出错
                $this->error(api_msg());
            }else{
                $this->success($result,api_msg());
            }
        }else{
            $this->error("未指定控制器或者操作!错误代码:103");
        }
    }


}
