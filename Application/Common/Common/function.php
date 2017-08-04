<?php
/**
 * Created by PhpStorm.
 * User: tiptimes
 * Date: 15-4-11
 * Time: 上午11:34
 */

const JDICMS_VERSION    = '1.1.141101'; //程序版本
const JDICMS_ADDON_PATH = './Addons/'; //插件目录
const ONETHINK_ADDON_PATH = './Addons/'; //兼容ONETHINK插件目录
const JDICMS_MOUDLE_PATH = './Modules/'; //模板目录
/**
 * 智能判断是否是模块内url访问
 * 模块内和外部都可用调用此方法
 * 如果是如果是非模块调用则和直接
 * 调用U函数效果一样，如果是模块
 * 内调用，则做相应的分发处理
 * @return mixed|string
 */
function _U($url, $param = array()){
    if(defined("__CURRENT_MODULE__") && !strpos($url,"#")){ //模块内访问
        if(!strpos($url,'/')){//当前控制器
            $url = __CURRENT_CONTROLLER__.'/'.$url;
        }
        $url = __CURRENT_MODULE__.'://'.$url;
        $url        = parse_url($url);
        $case       = C('URL_CASE_INSENSITIVE');
        $module     = $case ? parse_name($url['scheme']) : $url['scheme'];
        $controller = $case ? parse_name($url['host']) : $url['host'];
        $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

        /* 解析URL带的参数 */
        if(isset($url['query'])){
            parse_str($url['query'], $query);
            $param = array_merge($query, $param);
        }

        /* 基础参数 */
        $params = array(
            '_extend' =>'module',
            '_module'     => $module, //模块
            '_controller' => $controller, //控制器
            '_action'     => $action, //方法
        );
        $params = array_merge($params, $param); //添加额外参数
        return U('', $params);
    }else{
        return U($url,$param);
    }
}

/**
 * 配置数据模型得验证规则和自动完成规则
 * 通常用在模型得添加编辑操作中
 * @param $Model
 * @param $model_name
 * @return mixed
 */
function checkAttr($Model,$model_name){
    $fields     =   (array)get_model_attribute($model_name,false);
    $validate   =   $auto   =   array();
    foreach($fields as $key=>$attr){
        if($attr['is_must']){// 必填字段
            $validate[]  =  array($attr['name'],'require',$attr['title'].'必须!');
        }
        // 自动验证规则
        if(!empty($attr['validate_rule'])) {
            $validate[]  =  array($attr['name'],$attr['validate_rule'],$attr['error_info']?$attr['error_info']:$attr['title'].'验证错误',$attr['validate_condition'],$attr['validate_type'],$attr['validate_time']);
        }
        // 自动完成规则
        if(!empty($attr['auto_rule'])) {
            $auto[]  =  array($attr['name'],$attr['auto_rule'],$attr['auto_time'],$attr['auto_type']);
        }elseif('checkbox'==$attr['type']){ // 多选型
            $auto[] =   array($attr['name'],'arr2str',3,'function');
        }elseif(preg_match("/^date.*/",$attr['type'])){ // 日期型
            $auto[] =   array($attr['name'],'strtotime',3,'function');
        }
    }
    return $Model->validate($validate)->auto($auto);
}

/**
 * 生成插件控制器的访问url
 * @param string $url url
 * @param array $param 参数
 * @return string 处理后得url
 */
function addons_url($url, $param = array()){
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if(isset($url['query'])){
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        '_extend' =>'addons',
        '_addons'     => $addons,
        '_controller' => $controller,
        '_action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('', $params);
}

/**
 * 获得后台插件菜单
 */
function get_addons_menu(){
    $admin = array();
    $db_addons =  D('Addons')->where("status=1 AND has_adminlist=1")->field('title,name')->select();
    $name  = I('get.name',I('get._addons'));
    if($db_addons){
        $flag = false;
        foreach ($db_addons as $value) {
            if($name == $value['name']){
                $class = 'active';
                $flag = true;
            }else{
                $class = "";
            }
            $admin[] = array('title'=>$value['title'],'url'=>"AddonsBack/adminList?name={$value['name']}",
                'class'=>$class,'name'=>$value['name']);
        }
        if(!$flag && count($db_addons) >0){
            $admin[0]['class']='active';
            $_GET['name'] =$admin[0]['name'];
        }
    }else{
        return false;
    }
    return $admin;
}

/* 解析插件数据列表定义规则*/
function get_addonlist_field($data, $grid,$addon){
    // 获取当前字段数据
    foreach($grid['field'] as $field){
        $array  =   explode('|',$field);
        $temp  =    $data[$array[0]];
        // 函数支持
        if(isset($array[1])){
            $temp = call_user_func($array[1], $temp);
        }
        $data2[$array[0]]    =   $temp;
    }
    if(!empty($grid['format'])){
        $value  =   preg_replace_callback('/\[([a-z_]+)\]/', function($match) use($data2){return $data2[$match[1]];}, $grid['format']);
    }else{
        $value  =   implode(' ',$data2);
    }

    // 链接支持
    if(!empty($grid['href'])){
        $links  =   explode(',',$grid['href']);
        foreach($links as $link){
            $array  =   explode('|',$link);
            $href   =   $array[0];
            if(preg_match('/^\[([a-z_]+)\]$/',$href,$matches)){
                $val[]  =   $data2[$matches[1]];
            }else{
                $show   =   isset($array[1])?$array[1]:$value;
                // 替换系统特殊字符串
                $href   =   str_replace(
                    array('[DELETE]','[EDIT]','[ADDON]'),
                    array('del?ids=[id]&name=[ADDON]','edit?id=[id]&name=[ADDON]',$addon),
                    $href);

                // 替换数据变量
                $href   =   preg_replace_callback('/\[([a-z_]+)\]/', function($match) use($data){return $data[$match[1]];}, $href);

                $val[]  =   '<a href="'.U($href).'">'.$show.'</a>';
            }
        }
        $value  =   implode(' ',$val);
    }
    return $value;
}




/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null){

    //参数检查
    if(empty($action) || empty($model) || empty($record_id)){
        return '参数不能为空';
    }
    if(empty($user_id)){
        $user_id = is_login();
    }

    //查询行为,判断是否执行
    $action_info = M('Action')->getByName($action);
    if($action_info['status'] != 1){
        return '该行为被禁用或删除';
    }

    //插入行为日志
    $data['action_id']      =   $action_info['id'];
    $data['user_id']        =   $user_id;
    $data['action_ip']      =   ip2long(get_client_ip());
    $data['model']          =   $model;
    $data['record_id']      =   $record_id;
    $data['create_time']    =   NOW_TIME;

    //解析日志规则,生成日志备注
    if(!empty($action_info['log'])){
        if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
            $log['user']    =   $user_id;
            $log['record']  =   $record_id;
            $log['model']   =   $model;
            $log['time']    =   NOW_TIME;
            $log['data']    =   array('user'=>$user_id,'model'=>$model,'record'=>$record_id,'time'=>NOW_TIME);
            foreach ($match[1] as $value){
                $param = explode('|', $value);
                if(isset($param[1])){
                    $replace[] = call_user_func($param[1],$log[$param[0]]);
                }else{
                    $replace[] = $log[$param[0]];
                }
            }
            $data['remark'] =   str_replace($match[0], $replace, $action_info['log']);
        }else{
            $data['remark'] =   $action_info['log'];
        }
    }else{
        //未定义日志规则，记录操作url
        $data['remark']     =   '操作url：'.$_SERVER['REQUEST_URI'];
    }

    M('ActionLog')->add($data);

    if(!empty($action_info['rule'])){
        //解析行为
        $rules = parse_action($action, $user_id);
        //执行行为
        $res = execute_action($rules, $action_info['id'], $user_id);
    }
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self){
    if(empty($action)){
        return false;
    }

    //参数支持id或者name
    if(is_numeric($action)){
        $map = array('id'=>$action);
    }else{
        $map = array('name'=>$action);
    }

    //查询行为信息
    $info = M('Action')->where($map)->find();
    if(!$info || $info['status'] != 1){
        return false;
    }

    //解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
    $rules = $info['rule'];
    $rules = str_replace('{$self}', $self, $rules);
    $rules = explode(';', $rules);
    $return = array();
    foreach ($rules as $key=>&$rule){
        $rule = explode('|', $rule);
        foreach ($rule as $k=>$fields){
            $field = empty($fields) ? array() : explode(':', $fields);
            if(!empty($field)){
                $return[$key][$field[0]] = $field[1];
            }
        }
        //cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
        if(!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])){
            unset($return[$key]['cycle'],$return[$key]['max']);
        }
    }

    return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = array(), $action_id = null, $user_id = null){
    if(empty($rules) || empty($action_id) || empty($user_id)){
        return false;
    }

    $return = true;
    foreach ($rules as $rule){

        //检查执行周期
        $map = array('action_id'=>$action_id, 'user_id'=>$user_id);
        $map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
        $exec_count = M('ActionLog')->where($map)->count();
        if($exec_count > $rule['max']){
            continue;
        }

        //执行数据库操作
        $Model = M(ucfirst($rule['table']));
        $field = $rule['field'];
        $res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

        if(!$res){
            $return = false;
        }
    }
    return $return;
}
/**
 * 获取行为类型
 * @param integer $type 类型
 * @param bool $all 是否返回全部类型
 * @return mixed 结果
 */
function get_action_type($type, $all = false){
    $list = array(
        1=>'系统',
        2=>'用户',
    );
    if($all){
        return $list;
    }
    return $list[$type];
}


/**
 * 适配array_column函数
 */
if(!function_exists('array_column')){
    function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 获取表名（不含表前缀）
 * @param string $model_id
 * @return string 表名
 * @author huajie <banhuajie@163.com>
 */
function get_table_name($model_id = null){
    if(empty($model_id)){
        return false;
    }
    $Model = M('Model');
    $name = '';
    $info = $Model->getById($model_id);
    $name .= $info['name'];
    return $name;
}

/**
 * 获取属性信息并缓存
 * @param  integer $model_id    模型id
 * @param  bool  $group 是否分组
 * @return array  属性信息
 */
function get_model_attribute($model_id, $group = true){
    static $list;
    if(!is_numeric($model_id)){
        $model_id = M('Model')->getFieldByName($model_id,'id');
    }

    /* 非法ID */
    if(empty($model_id) || !is_numeric($model_id)){
        return '';
    }


    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('attribute_list');
    }

    /* 获取属性 */
    if(!isset($list[$model_id])){
        $map = array('model_id'=>$model_id);
        $extend = M('Model')->getFieldById($model_id,'extend');

        if($extend){
            $map = array('model_id'=> array("in", array($model_id, $extend)));
        }
        $info = M('Attribute')->where($map)->select();
        $list[$model_id] = $info;
        //S('attribute_list', $list); //更新缓存
    }

    $attr = array();
    foreach ($list[$model_id] as $value) {
        $attr[$value['id']] = $value;
    }

    if($group){
        $sort  = M('Model')->getFieldById($model_id,'field_sort');

        if(empty($sort)){   //未排序
            $group = array(1=>array_merge($attr));
        }else{
            $group = json_decode($sort, true);

            $keys  = array_keys($group);
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
            }

            if(!empty($attr)){
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
            }
        }
        $attr = $group;
    }
    return $attr;
}



/**
 * 根据条件字段获取指定表的数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @param string $table 需要查询的表
 * @author huajie <banhuajie@163.com>
 */
function get_table_field($value = null, $condition = 'id', $field = null, $table = null){
    if(empty($value) || empty($table)){
        return false;
    }
    //拼接参数
    $map[$condition] = $value;
    $info = M(ucfirst($table))->where($map);
    if(empty($field)){
        $info = $info->field(true)->find();
    }else{
        $info = $info->getField($field);
    }
    return $info;
}


/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
    \Think\Hook::listen($hook,$params);
}

/**
 * 获得模块类全称
 * @param string $name  类名缩写
 * @return string 类的全称
 */
function get_module_class($name){
    $class = "Modules\\{$name}\\{$name}Module";
    return $class;
}

/**
 * 调用插件
 * 直接通过插件名称去调用插件
 * @param $name
 * @param null $params
 * @return mixed
 */
function plugin($name,&$params=NULL) {
    $class = "Addons\\{$name}\\{$name}Addon";
    if(class_exists($class)){
        $addon = new $class;
        if(!check_addons($name)){
            return '';
        }
        return $addon->$name($params);
    }else{
        return '';
    }
}

/**
 * 获取插件类的类名
 * @param string $name 插件名
 * @return string 插件类名
 */
function get_addon_class($name){
    $class = "Addons\\{$name}\\{$name}Addon";
    return $class;
}
/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 * @return array 配置数组
 */
function get_addon_config($name){
    $class = get_addon_class($name);
    if(class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    }else {
        return array();
    }
}

/**
 * 检测插件是否存在并且可用
 * @param string $name 插件名称(不是插件的类全称)
 * @return bool 是否可用
 */
function check_addons($name){
    static $addons_status = array();
    //检测插件是否已经安装并且启用
    if(!isset($addons_status[$name])){
        $result = M('Addons')->where(array('name'=>$name))->find();
        if($result['status'] == 1){
            $addons_status[$name]  = 1;
        }else{
            $addons_status[$name] = 0;
        }
    }
    if($addons_status[$name] !== 1 ){
        return false;
    }else{
        return true;
    }
}

//手机访问
function ismobile() {
	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
		return true;
	 
	//此条摘自TPM智能切换模板引擎，适合TPM开发
	if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
		return true;
	//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	if (isset ($_SERVER['HTTP_VIA']))
		//找不到为flase,否则为true
		return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
	//判断手机发送的客户端标志,兼容性有待提高
	if (isset ($_SERVER['HTTP_USER_AGENT'])) {
		$clientkeywords = array(
				'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
		);
		//从HTTP_USER_AGENT中查找手机浏览器的关键字
		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
			return true;
		}
	}
	//协议法，因为有可能不准确，放到最后判断
	if (isset ($_SERVER['HTTP_ACCEPT'])) {
		// 如果只支持wml并且不支持html那一定是移动设备
		// 如果支持wml和html但是wml在html之前则是移动设备
		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
			return true;
		}
	}
	return false;
}