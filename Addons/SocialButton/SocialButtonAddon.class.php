<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------


namespace Addons\SocialButton;
use Common\Controller\Addon;

/**
 * 通用社交化评论插件
 * @author thinkphp
 */

class SocialButtonAddon extends Addon{
    public $info = array(
        'name'=>'SocialButton',
        'title'=>'点赞按钮',
        'description'=>'集成点赞功能',
        'status'=>1,
        'author'=>'tiptimes',
        'version'=>'0.1'
    );


    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

    public function SocialButton(){
        $this->display('button');
    }
}