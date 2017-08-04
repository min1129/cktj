<?php

namespace Addons\ApiDoc;
use Common\Controller\Addon;

class ApiDocAddon extends Addon{

    public $info = array(
        'name'=>'ApiDoc',
        'title'=>'api文档生成器',
        'description'=>'生成模块api文档',
        'status'=>1,
        'author'=>'tiptimes',
        'version'=>'0.1'
    );

    public $custom_adminlist = 'adminlist.html';
    public $has_admin = true;

    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

    public function admin_before($controller){
        $list       =   M('Module')->where(array('status'=>1))->select();
        $controller->assign('list',$list);
    }
}