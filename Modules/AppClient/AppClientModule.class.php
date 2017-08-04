<?php

namespace Modules\AppClient;
use Common\Controller\Module;

/**
 *
 */
class AppClientModule extends Module{
    public $info = array(
        'name'=>'AppClient',
        'title'=>'客户端',
        'description'=>'android或者ios客户端管理',
        'status'=>1,
        'author'=>'tp',
        'version'=>'0.1'
    );

    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

    public function update(){

    }
}