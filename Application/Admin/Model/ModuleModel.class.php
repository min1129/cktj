<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 插件模型
 * @author yangweijie <yangweijiester@gmail.com>
 */

class ModuleModel extends Model {

    /**
     * 查找后置操作
     */
    protected function _after_find(&$result,$options) {

    }

    protected function _after_select(&$result,$options){

        foreach($result as &$record){
            $this->_after_find($record,$options);
        }
    }
    /**
     * 文件模型自动完成
     * @var array
     */
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取模块列表
     * @param string $module_dir
     */
    public function getList($module_dir = ''){
        if(!$module_dir)
            $module_dir = JDICMS_MOUDLE_PATH;
        $dirs = array_map('basename',glob($module_dir.'*', GLOB_ONLYDIR));
        if($dirs === FALSE || !file_exists($module_dir)){
            $this->error = '模块目录不可读或者不存在';
            return FALSE;
        }
        $modules			=	array();
		$where['name']	=	array('in',$dirs);
		$list			=	$this->where($where)->field(true)->select();
		foreach($list as $module){
			$module['uninstall']		=	0;
			$modules[$module['name']]	=	$module;
		}
        foreach ($dirs as $value) {
            if(!isset($modules[$value])){
				$class = get_module_class($value);
				if(!class_exists($class)){ // 实例化插件失败忽略执行
					\Think\Log::record('模块'.$value.'的入口文件不存在！');
                    continue;
				}
                $obj    =   new $class;
				$modules[$value]	= $obj->info;
				if($modules[$value]){
					$modules[$value]['uninstall'] = 1;
                    unset($modules[$value]['status']);
				}
			}
        }
        int_to_string($modules, array('status'=>array(-1=>'损坏', 0=>'禁用', 1=>'启用', null=>'未安装')));
        $modules = list_sort_by($modules,'uninstall','desc');
        return $modules;
    }
}
