<?php
namespace Admin\Controller;
use Common\Controller\AdminController;
/**
 * 模块管理页面
 * @author lihao<lh@tiptime.com>
 */
class ModuleController extends AdminController {

    /**
     * 插件列表
     */
    public function index(){
        $this->meta_title = '模块列表';
        $list       =   D('Module')->getList();
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        MK();
        $this->display();
    }

    /**
     * 启用模块
     */
    public function enable(){
        $id     =   I('id');
        $msg    =   array('success'=>'启用成功', 'error'=>'启用失败');
        $this->resume('Module', "id={$id}", $msg);
    }

    /**
     * 禁用模块
     */
    public function disable(){
        $id     =   I('id');
        $msg    =   array('success'=>'禁用成功', 'error'=>'禁用失败');
        $this->forbid('Module', "id={$id}", $msg);
    }

    /**
     * 设置插件页面
     */
    public function config(){
        $id     =   (int)I('id');
        $module  =   M('Module')->find($id);
        if(!$module)
            $this->error('模块未安装');
        $module_class = get_module_class($module['name']);
        if(!class_exists($module_class))
            trace("插件{$module['name']}无法实例化,",'ADDONS','ERR');
        $data  =   new $module_class;
        $module['module_path'] = $data->module_path;
        $module['custom_config'] = $data->custom_config;
        $this->meta_title   =   '模块配置-'.$data->info['title'];
        $db_config = $module['config'];
        $module['config'] = include $data->config_file;
        if($db_config){
            $db_config = json_decode($db_config, true);
            foreach ($module['config'] as $key => $value) {
                if($value['type'] != 'group'){
                    $module['config'][$key]['value'] = $db_config[$key];
                }else{
                    foreach ($value['options'] as $gourp => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            $module['config'][$key]['options'][$gourp]['options'][$gkey]['value'] = $db_config[$gkey];
                        }
                    }
                }
            }
        }
        $this->assign('data',$module);
        if($module['custom_config'])
            $this->assign('custom_config', $this->fetch($module['module_path'].$module['custom_config']));
        $this->display();
    }

    /**
     * 保存插件设置
     */
    public function saveConfig(){
        $id     =   (int)I('id');
        $config =   I('config');
        $flag = M('Module')->where("id={$id}")->setField('config',json_encode($config));
        if($flag !== false){
            $this->success('保存成功', LK());
        }else{
            $this->error('保存失败');
        }
    }


    /**
     * 安装模块
     */
    public function install(){
        $module_name     =   trim(I('module_name'));
        $class          =   get_module_class($module_name);
        if(!class_exists($class))
            $this->error('模块不存在');
        $module  =   new $class;
        $info = $module->info;
        if(!$info || !$module->checkInfo())//检测信息的正确性
            $this->error('模块信息缺失');
        session('module_install_error',null);
        $install_flag   =   $module->install();
        if(!$install_flag){
            $this->error('执行模块预安装操作失败'.session('module_install_error'));
        }
        $moduleModel    =   D('Module');
        $data           =   $moduleModel->create($info);
        if(!$data)
            $this->error($moduleModel->getError());
        if($moduleModel->add($data)){
            $config         =   array('config'=>json_encode($module->getConfig()));
            if($config){
                if(!empty($module->enter_controller)){
                    $enter_controller = $module->enter_controller;
                }else{
                    $enter_controller = "index";
                }
                if(!empty($module->enter_action)){
                    $enter_action = $module->enter_action;
                }else{
                    $enter_action = "index";
                }

                $moduleModel->where("name='{$module_name}'")->save($config);
                $Menu = D('Menu');
                $menu_data['title'] = $info['title'];
                $menu_data['pid'] = 0;
                $menu_data['url'] = 'dispatch/execute_module?_extend=module&_module='.$info['name']
                                    .'&_controller='.$enter_controller.'&_action='.$enter_action;
                $menu_data['module'] = $module_name;
                if($Menu->add($menu_data)){
                    $this->success('安装成功!');
                }else{
                    $this->error('菜单更新出错!');
                }
            }
        }else{
            $this->error('写入模块数据失败');
        }
    }

    /**
     * 卸载模块
     */
    public function uninstall(){
        $moduleModel    =   M('Module');
        $id             =   trim(I('id'));
        $db_module      =   $moduleModel->find($id);
        $class          =   get_module_class($db_module['name']);
        $this->assign('jumpUrl',U('index'));
        if(!$db_module || !class_exists($class))
            $this->error('模块不存在');
        session('module_uninstall_error',null);
        $module =   new $class;
        $uninstall_flag =   $module->uninstall();
        if(!$uninstall_flag)
            $this->error('执行模块预卸载操作失败'.session('module_uninstall_error'));
        $delete = $moduleModel->where("name='{$db_module['name']}'")->delete();
        if($delete === false){
            $this->error('卸载模块失败');
        }else{
            $title = $module->info['title'];
            M('Menu')->where(array('pid'=>0,'title'=>$title))->delete();
            $this->success('卸载模块成功');
        }
    }
}
