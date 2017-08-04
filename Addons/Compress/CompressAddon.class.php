<?php

namespace Addons\Compress;
use Common\Controller\Addon;

class CompressAddon extends Addon{

    public $info = array(
        'name'=>'Compress',
        'title'=>'前端发布工具',
        'description'=>'生成生产环境的前端代码',
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
        $controller->assign('list',get_temp_list());
    }
}