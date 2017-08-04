<?php

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 * @author huajie <banhuajie@163.com>
 */
function get_status_title($status = null){
    if(!isset($status)){
        return false;
    }
    switch ($status){
        case -1 : return    '已删除';   break;
        case 0  : return    '禁用';     break;
        case 1  : return    '正常';     break;
        case 2  : return    '待审核';   break;
        default : return    false;      break;
    }
}

// 获取数据的状态操作
function show_status_op($status) {
    switch ($status){
        case 0  : return    '启用';     break;
        case 1  : return    '禁用';     break;
        case 2  : return    '审核';		break;
        default : return    false;      break;
    }
}

/**
 * 获取列表数据定义
 * @param $data
 * @param $grid
 * @return mixed|string
 */
function get_list_field($data, $grid){
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
    if('title' == $grid['field'][0] && '目录' == $data['type'] ){
        // 目录类型自动设置子文档列表链接
        $grid['href']   =   '[LIST]';
    }
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
                    array('[DELETE]','[EDIT]','[LIST]'),
                    array('setstatus?status=-1&ids=[id]',
                        'edit?id=[id]&model=[model_id]&cate_id=[category_id]',
                        'index?pid=[id]&model=[model_id]&cate_id=[category_id]'),
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

// 获取模型名称
function get_model_by_id($id,$filed='title'){
    if($id==0){
        return '无';
    }else{
        return $model = M('Model')->getFieldById($id,$filed);
    }
}

/**
 * 动态扩展左侧菜单,base.html里用到
 * @author 朱亚杰 <zhuyajie@topthink.net>
 */
function extra_menu($extra_menu,&$base_menu){
    $ext = $extra_menu['ext'];
    foreach ($ext as $key=>$group){
        if( isset($base_menu['child'][$key]) ){
            $base_menu['child'][$key] = array_merge( $base_menu['child'][$key], $group);
        }else{
            $base_menu['child'][$key] = $group;
        }
    }
    if(empty($base_menu['group_class'])){
        $base_menu['group_class'][$extra_menu['group']]= 'open active';
    }
}


/**
 * 获取行为数据
 * @param string $id 行为id
 * @param string $field 需要获取的字段
 * @author huajie <banhuajie@163.com>
 */
function get_action($id = null, $field = null){
    if(empty($id) && !is_numeric($id)){
        return false;
    }
    $list = S('action_list');
    if(empty($list[$id])){
        $map = array('status'=>array('gt', -1), 'id'=>$id);
        $list[$id] = M('Action')->where($map)->field(true)->find();
    }
    return empty($field) ? $list[$id] : $list[$id][$field];
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

// 分析枚举类型字段值 格式 a:名称1,b:名称2
function parse_field_attr($string) {
    if(0 === strpos($string,':')){
        // 采用函数定义
        return   eval('return '.substr($string,1).';');
    }elseif(0 === strpos($string,'[')){
        // 支持读取配置参数（必须是数组类型）
        return C(substr($string,1,-1));
    }elseif(0 === strpos($string,'{')){
        $funName = substr($string,1,-1);//支持函数(必须是数组类型)
        return $funName();
    }

    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

/**
 * 处理文档列表显示
 * @param array $list 列表数据
 * @param integer $model_id 模型id
 * @return array
 */
function parseDocumentList($list,$model_id=null){
    $model_id = $model_id ? $model_id : 1;
    $attrList = get_model_attribute($model_id,false,'id,name,type,extra');
    // 对列表数据进行显示处理
    if(is_array($list)){
        foreach ($list as $k=>$data){
            foreach($data as $key=>$val){
                if(isset($attrList[$key])){
                    $extra      =   $attrList[$key]['extra'];
                    $type       =   $attrList[$key]['type'];
                    if('select'== $type || 'checkbox' == $type || 'radio' == $type || 'bool' == $type) {
                        // 枚举/多选/单选/布尔型
                        $options    =   parse_field_attr($extra);
                        if($options && array_key_exists($val,$options)) {
                            $data[$key]    =   $options[$val];
                        }
                    }elseif('date'==$type){ // 日期型
                        $data[$key]    =   date('Y-m-d',$val);
                    }elseif('datetime' == $type){ // 时间型
                        $data[$key]    =   date('Y-m-d H:i',$val);
                    }
                }
            }
            $data['model_id'] = $model_id;
            $list[$k]   =   $data;
        }
    }
    return $list;
}


// 获取属性类型信息
function get_attribute_type($type=''){
    // TODO 可以加入系统配置
    static $_type = array(
        'num'       =>  array('数字','int(10) UNSIGNED NOT NULL'),
        'string'    =>  array('字符串','varchar(255) NOT NULL'),
        'textarea'  =>  array('文本框','text NOT NULL'),
        'date'      =>  array('日期','int(10) NOT NULL'),
        'datetime'  =>  array('时间','int(10) NOT NULL'),
        'date_view_4'=> array('年份视图日期','int(10) NOT NULL'),
        'date_3'=> array('年月日期','int(10) NOT NULL'),
        'bool'      =>  array('布尔','tinyint(2) NOT NULL'),
        'select'    =>  array('枚举','char(50) NOT NULL'),
        'radio'     =>  array('单选','char(10) NOT NULL'),
        'checkbox'  =>  array('多选','varchar(100) NOT NULL'),
        'editor'    =>  array('编辑器','text NOT NULL'),
        'simpleEditor'    =>  array('简单编辑器','text NOT NULL'),
        'picture'   =>  array('上传图片','int(10) UNSIGNED NOT NULL'),
        'multiPicture'=>array('多图片上传','text'),
        'file'      =>  array('上传附件','int(10) UNSIGNED NOT NULL'),
        'color'      =>  array('颜色','char(10)  NOT NULL'),
        'place'     =>array('地址','char(255)  NOT NULL'),
        'point'     =>array('坐标','char(80)  NOT NULL'),
        'keyword'  =>array('关键字(默认取title和content字段作为提取对象)','varchar(255) NOT NULL'),
        'autoImage' =>array('自动提取图片(默认取content字段作为提取对象)','mediumint(8) UNSIGNED  NOT NULL'),
        'autoText' =>array('自动完成内容(默认取content字段作为提取对象)','varchar(255)  NOT NULL')
    );
    return $type?$_type[$type][0]:$_type;
}


/**
 * 系统默认的模型分组
 * @param string $type
 * @return mixed
 */
function get_model_type($type=''){
    static $_type = array('内容模型','用户模型');
    return $type!==''?$_type[$type]:$_type;;
}

/**
 * 获得系统所有模型,不包括没有建表的模型
 */
function get_models(){
    $models = D('model')->where(array('status'=>array('gt',0)))->field('name,id,title')->select();
    $tables =  D('model')->getTables();
    foreach($models as $k=>$model){
        if(!in_array(C('DB_PREFIX').$model['name'],$tables)){
            unset($models[$k]);
        }
    }
    return $models;
}

/**
 * 获得指定栏目的模型记录
 * @param $catid
 * @return mixed
 */
function get_model_by_catid($catid){
    $Category = D("Category");
    $cat = $Category->where(array('id'=>$catid))->find();
    $model = M("model")->where(array('id'=>$cat['model_id']))->find();
    return $model;
}


/**
 * 获取配置的类型
 * @param string $type 配置类型
 * @return string
 */
function get_config_type($type=0){
    $list = C('CONFIG_TYPE_LIST');
    return $list[$type];
}

/**
 * 获取配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_config_group($group=0){
    $list = C('CONFIG_GROUP_LIST');
    return $group?$list[$group]:'';
}


/**
 * 根据条件字段获取数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 * @author huajie <banhuajie@163.com>
 */
function get_document_field($value = null, $condition = 'id', $field = null){
    if(empty($value)){
        return false;
    }

    //拼接参数
    $map[$condition] = $value;
    $info = M('Model')->where($map);
    if(empty($field)){
        $info = $info->field(true)->find();
    }else{
        $info = $info->getField($field);
    }
    return $info;
}

//基于数组创建目录和文件
function create_dir_or_files($files){
    foreach ($files as $key => $value) {
        if(substr($value, -1) == '/'){
            mkdir($value);
        }else{
            @file_put_contents($value, '');
        }
    }
}


/**
 * 获取所有的模板
 * @return mixed
 */
function get_all_temp(){
    $path = temp_path().'/Home/Index';
    $files = scandir(realpath($path));
    $result['category'] = array();
    $result['list'] = array();
    $result['content'] = array();
    foreach($files as $key=>$value){
        $value = substr($value,0,strpos($value,'.'));
        if(strpos($value, 'category') === 0){
            $result['category'][] = $value;
        }elseif(strpos($value, 'list') === 0){
            $result['list'][] = $value;
        }elseif(strpos($value, 'content') === 0){
            $result['content'][] = $value;
        }

    }
    return $result;
}

/**
 * 字段处理 便于数据库匹配查询
 * @param $pos
 * @return string
 */
function position_parse($pos){
    $srt = arr2str($pos);
    if(!empty($srt)){
        $srt.=',';
    }else{
       $srt=0;
    }
    return $srt;
}

/**
 * 获取分组名称
 * @param $key
 * @return mixed
 */
function get_link_group($key){
   $re =  C('LINK_GROUP');
   return $re[$key];
}

/**
 * 获取审核人名称
 * @param $data_id
 * @param $table_name
 * @return mixed|string
 */
function get_verify_user($data_id,$table_name){
    $result = M('verify')->where(array('topic_table'=>$table_name,'topic_id'=>$data_id))->find();
    if($result){
        return get_nickname($result['uid']);
    }else{
        return '--';
    }
}

/**
 * 设置栏目权重
 * @param $value
 * @return int
 */
function setWeight($value){
    if(empty($value)){
        $value = 0;
        if($_POST['is_post']){
            $value+=10;
        }
    }
    return $value;
}
