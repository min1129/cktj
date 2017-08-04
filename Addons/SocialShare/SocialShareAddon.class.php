<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------


namespace Addons\SocialShare;
use Common\Controller\Addon;

/**
 * 通用社交化评论插件
 * @author thinkphp
 */

class SocialShareAddon extends Addon{
    public $info = array(
        'name'=>'SocialShare',
        'title'=>'分享插件',
        'description'=>'集成分享功能',
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

    public function SocialShare(){
        $this->assign('addons_config', $this->getConfig());
        $this->display('share');
    }
}