<?php

namespace Modules\JDI;
use Common\Controller\Module;

/**
 *
 */
class JDIModule extends Module{
    public $info = array(
        'name'=>'JDI',
        'title'=>'公共模块',
        'description'=>'程序内部公用模块，请不要安装',
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