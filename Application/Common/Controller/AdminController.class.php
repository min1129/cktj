<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 14/12/11
 * Time: 上午9:23
 */
namespace Common\Controller;
use Think\Controller;
use Common\Model\AuthGroupModel;

/**
 * 后台控制器基类
 * Class AdminController
 * @package Common\Controller
 * @author lihao 修改<lh@tiptime.com>
 */
class AdminController extends DataBaseController {
    /**
     * 后台控制器初始化
     */
    protected function _initialize(){

        parent::_initialize();

        // 获取当前用户ID
        define('UID',is_login());

        if( !UID ){// 还没登录 跳转到登录页面
            $this->redirect('Admin/public/login');
        }
        if(user_field('type') != 0){
            $this->error("无权限访问!",U('public/login'));
        }
        // 是否是超级管理员
        define('IS_ROOT',   is_administrator());

        if(!IS_ROOT && C('ADMIN_ALLOW_IP')){
            // 检查IP地址访问
            if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))){
                $this->error('403:禁止访问');
            }
        }


        // 检测访问权限
        $access =   $this->accessControl();
        if ( $access === false ) {
            $this->error('403:禁止访问');
        }elseif( $access === null ){
            $dynamic        =   $this->checkDynamic();//检测分类栏目有关的各项动态权限
            if( $dynamic === null ){
                //检测非动态权限
                $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
                if (!$this->checkRule($rule,array('in','1,2')) ){
                    $this->error('未授权访问!');
                }
            }elseif( $dynamic === false ){

                $this->error('未授权访问!');
            }
        }
        $this->assign('__MENU__', $this->getMenus());
    }


    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     *
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    protected function checkDynamic(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }

    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function accessControl(){
        $extend = I('get._extend');
        if(CONTROLLER_NAME == "AddonsBack"){ //后台菜单操作显示
            $menu = get_addons_menu();
            if(!$menu){
                $this->error("您还未装带有插件的后台!");
            }
            $this->assign('_extra_menu',array(
                'ext'=>array('已装插件'=>$menu),
                'group'=>'已装插件'
                ));
            return true;
        }

        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }

        if($extend == 'addons'){ //插件调度
            $rule  = strtolower('Admin/AddonsBack/adminList');
            if (!$this->checkRule($rule,array('in','1,2')) ){
                false;
            }
        }

        if($extend == "module"){//模块调度
            $module = $_GET['_module'];
            return $this->checkModules($module);
        }

        $allow = C('ALLOW_VISIT');
        $deny  = C('DENY_VISIT');
        $check = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);

        if(strtolower(CONTROLLER_NAME)=='tool'){//工具类
            return true;
        }

        if ( !empty($deny)  && in_array_case($check,$deny) ) {
            return false;//非超管禁止访问deny中的方法
        }
        if ( !empty($allow) && in_array_case($check,$allow) ) {
            return true;
        }
        return null;//需要检测节点权限
    }

    /**
     * 检测模块权限
     * @param $module
     * @return bool
     */
    final protected function  checkModules($module){
        static $auth_modules = null;
        if(IS_ROOT){
            return true;
        }
        if($auth_modules === null){
            $auth_modules = AuthGroupModel::getAuthModules(UID);
        }
        $map['status'] = 1;
        $map['name'] = $module;
        $module =  M('Module')->where($map)->field('id')->find();
        if(!$module){ //模块被禁用,或不存在
            return false;
        }elseif(in_array($module['id'],$auth_modules)){
           return true; //可以访问
       }else{
            return false; //没有权限
        }
    }

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final  protected  function getMenus($controller=CONTROLLER_NAME){

       $controller = strtolower($controller);
        // $menus  =   session('ADMIN_MENU_LIST'.$controller);
       if(empty($menus)){
            // 获取主菜单
        $where['pid']   =   0;
            //$where['hide']  =   0;
            if(!C('DEVELOP_MODE')){ // 是否开发者模式
                $where['is_dev']    =   0;
            }
            $menus['main']  =   M('Menu')->where($where)->order('sort asc')->select();
            $menus['child'] = array(); //设置子节点
            /**
             * 动态设置主菜单权限和url
             */
            foreach($menus['main'] as $mk=>$main_menu){
                $where['pid']   =   $main_menu['id'];
                $where['hide'] = 0;
                if(!C('DEVELOP_MODE')){ // 是否开发者模式
                    $where['is_dev']    =   0;
                }
                if($main_menu['module'] != 'Admin'){ //扩展模块
                    if(!$this->checkModules($main_menu['module'])){
                        unset($menus['main'][$mk]);
                    }
                    continue;
                }

                $mmenus = M('Menu')->where($where)->order('sort asc')->select();
                if($mmenus){
                    $flag = false;
                    foreach($mmenus as $mm){
                        if($this->checkRule(MODULE_NAME.'/'.$mm['url'],1,'url') ){
                            $menus['main'][$mk]['url']= $mm['url'];
                            $flag = true; //有子菜单
                            break;
                        }
                    }
                    if(!$flag){ //没有子菜单
                        unset($menus['main'][$mk]);
                    }
                }elseif($main_menu['url']=='Public/index'){
                    unset($menus['main'][$mk]);
                }else{
                    if(!$this->checkRule(MODULE_NAME.'/'.$main_menu['url'])){
                        unset($menus['main'][$mk]);
                    }
                }
            }

            //模块访问
            if(ACTION_NAME=='execute_module'){
                define("REAL_ACTION_NAME",$_GET['_action']);
                $module = $_GET['_module'];
                //高亮主菜单
                foreach ($menus['main'] as $key => $item) {
                    if (!is_array($item) || empty($item['title']) || empty($item['url']) ) {
                        $this->error('控制器基类$menus属性元素配置有误');
                    }
                    // 获取当前主菜单的子菜单项
                    if($item['module'] == $module){
                        $menus['main'][$key]['class']='active';
                    }
                }
                return $menus;
            }else{
                define("REAL_ACTION_NAME",ACTION_NAME);
            }

            $controller = lcfirst($controller);
            //高亮主菜单
            $current = M('Menu')->where("url like '{$controller}/".ACTION_NAME."'")->field('id')->find();

            if($current){
                $nav = D('Menu')->getPath($current['id']);
                $nav_first_title = $nav[0]['title'];

                foreach ($menus['main'] as $key => $item) {
                    if (!is_array($item) || empty($item['title']) || empty($item['url']) ) {
                        $this->error('控制器基类$menus属性元素配置有误');
                    }
                    if( stripos($item['url'],MODULE_NAME)!==0 ){
                        $item['url'] = MODULE_NAME.'/'.$item['url'];
                    }

                    // 获取当前主菜单的子菜单项
                    if($item['title'] == $nav_first_title){
                        $menus['group_class'] = array();

                        $menus['main'][$key]['class']='active';
                        //生成child树
                        $groups = M('Menu')->where("pid = {$item['id']}")->distinct(true)->field("`group`")->select();
                        if($groups){
                            $groups = array_column($groups, 'group');
                        }else{
                            $groups =   array();
                        }

                        //获取二级分类的合法url
                        $where          =   array();
                        $where['pid']   =   $item['id'];
                        $where['hide']  =   0;
                        if(!C('DEVELOP_MODE')){ // 是否开发者模式
                            $where['is_dev']    =   0;
                        }
                        $second_urls = M('Menu')->where($where)->getField('id,url');

                        if(!IS_ROOT){
                            // 检测菜单权限
                            $to_check_urls = array();
                            foreach ($second_urls as $key=>$to_check_url) {
                                if( stripos($to_check_url,MODULE_NAME)!==0 ){
                                    $rule = MODULE_NAME.'/'.$to_check_url;
                                }else{
                                    $rule = $to_check_url;
                                }
                                if($this->checkRule($rule, 1,null))
                                    $to_check_urls[] = $to_check_url;
                            }
                        }
                        // 按照分组生成子菜单树
                        foreach ($groups as $g) {
                            $map = array('group'=>$g);
                            if(isset($to_check_urls)){
                                if(empty($to_check_urls)){
                                    // 没有任何权限
                                    continue;
                                }else{
                                    $map['url'] = array('in', $to_check_urls);
                                }
                            }
                            $map['pid'] =   $item['id'];
                            $map['hide']    =   0;
                            if(!C('DEVELOP_MODE')){ // 是否开发者模式
                                $map['is_dev']  =   0;
                            }
                            $menuList = M('Menu')->where($map)->field('id,pid,title,url,tip')->order('sort asc')->select();
                            $group_class = '';
                            //高亮二级菜单

                            foreach($menuList as $k=>$menu){
                                $is_break = false;
                                foreach($nav as $cur_nav){
                                    if($cur_nav['url']===$menu['url']){
                                        $menuList[$k]['class']= 'active';
                                        $group_class = 'open active';
                                        $is_break=true;
                                        break;
                                    }
                                }
                                if($is_break){
                                    break;
                                }
                            }
                            $menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                            if(!empty($group_class)){
                                $menus['group_class'][$g] = $group_class;
                            }
                        }
                        if($menus['child'] === array()){
                            //$this->error('主菜单下缺少子菜单，请去系统=》后台菜单管理里添加');
                        }
                    }
                }
            }
           // session('ADMIN_MENU_LIST'.$controller,$menus);
        }
        return $menus;
    }

    /**
     * 返回后台节点数据
     * @param boolean $tree    是否返回多维数组结构(生成菜单时用到),为false返回一维数组(生成权限节点时用到)
     * @retrun array
     *
     * 注意,返回的主菜单节点数组中有'controller'元素,以供区分子节点和主节点
     *
     * @author 朱亚杰 <xcoolcc@gmail.com>
     */
    final protected function returnNodes($tree = true){
        static $tree_nodes = array();
        if ( $tree && !empty($tree_nodes[(int)$tree]) ) {
            return $tree_nodes[$tree];
        }
        if((int)$tree){

            $list = M('Menu')->field('id,pid,title,url,tip,hide')->order('sort asc')->select();
            foreach ($list as $key => $value) {
                if( stripos($value['url'],MODULE_NAME)!==0 ){
                    $list[$key]['url'] = MODULE_NAME.'/'.$value['url'];
                }
            }
            $nodes = list_to_tree($list,$pk='id',$pid='pid',$child='operator',$root=0);
            foreach ($nodes as $key => $value) {
                if(!empty($value['operator'])){
                    $nodes[$key]['child'] = $value['operator'];
                    unset($nodes[$key]['operator']);
                }
            }
        }else{
            $nodes = M('Menu')->field('title,url,tip,pid')->order('sort asc')->select();
            foreach ($nodes as $key => $value) {
                if( stripos($value['url'],MODULE_NAME)!==0 ){
                    $nodes[$key]['url'] = MODULE_NAME.'/'.$value['url'];
                }
            }
        }
        $tree_nodes[(int)$tree]   = $nodes;
        return $nodes;
    }


}