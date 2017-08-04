<?php

namespace Addons\LiuYan;
use Common\Controller\Addon;
use Think\Db;
/**
 * 留言插件
 * @author quick
 */

    class LiuYanAddon extends Addon{

        public $info = array(
            'name'=>'LiuYan',
            'title'=>'留言',
            'description'=>'留言插件',
            'status'=>1,
            'author'=>'tiptimes',
            'version'=>'0.1'
        );
        
        public $addon_path = './Addons/LiuYan/';
        public $custom_adminlist = 'adminlist.html';
//        public $has_admin = true;
        /**
         * 配置列表页面
         */
        public $admin_list = array(
            'listKey' => array(
                'name'=>'姓名',
                'content'=>'留言内容',
                'email'=>'邮箱',
                'phone'=>'联系电话',
                'create_time'=>'创建时间',
                'status' => '状态',
            ),
            'model'=>'Liuyan',
            'order'=>'create_time desc'
        );
        /**
         * 安装函数
         * @see \Common\Controller\Addons::install()
         */
		public function table_name(){
			$db_prefix = C('DB_PREFIX');
			return $db_prefix;
		}
		
		
        public function install(){
        	$sql=<<<SQL
CREATE TABLE IF NOT EXISTS `{$this->table_name()}liuyan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `content` text NOT NULL DEFAULT '' COMMENT '内容',
  `email` char(20) NOT NULL DEFAULT '' COMMENT '邮箱',
  `phone` char(20) NOT NULL DEFAULT '' COMMENT '电话',
  `name` char(20) NOT NULL DEFAULT '1' COMMENT '姓名',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建日期',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（-1：删除，1：正常）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='留言表' ;
SQL;
            D()->execute($sql);
            return true;
        }

        /**
         * 卸载函数
         * @see \Common\Controller\Addons::uninstall()
         */
        public function uninstall(){
            $sql = "DROP TABLE IF EXISTS `".$this->table_name()."liuyan`;";
            D()->execute($sql);
            return true;
        }

        public function LiuYan(){
            $this->display('widget');
        }
}