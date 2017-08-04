<?php
/**
 * Created by PhpStorm.
 * User: tiptimes
 * Date: 15-4-11
 * Time: 上午11:29
 */

/**
 * 调用系统的API接口方法（静态方法，也可以调用插件和模块接口，不指明的话则调用common下的系统接口）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $array 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 * @return mixed 调用结果
 */
function api($array,$vars=array()){
    if(!is_array($array)){
        $array     = explode('/',$array);
    }

    //方法
    $method    = array_pop($array);
    //类
    $class_name = array_pop($array);
    //模块
    $module    = $array? array_pop($array) : 'Common';
    //组件
    $component = array_pop($array);
    if($component){ //扩展插件或者模块接口
        $module = $component.'\\'.$module;
    }

    //拼接成类的全称
    $class = $module.'\\Api\\'.$class_name.'Api';
    try{
        //获得反射对象
        $reflect_class = new ReflectionClass($class);
        //是否含义请求方法
        if($reflect_class->hasMethod($method)){
            $reflect_method = $reflect_class->getMethod($method); //获得请求方法反射对象
            $params =  $reflect_method->getParameters(); //获取方法参数
            //请求参数和方法参数对应填充
            $real_vars = array();
            foreach ($params as $param){
                $name = $param->getName();  //获得参数名称
                if(isset($vars[$name])){
                    $real_vars[] = $vars[$name];
                }elseif($param->isDefaultValueAvailable()){
                    $real_vars[] = $param->getDefaultValue();
                }else{
                    api_msg("参数".$param->getName()."必须填写");
                    return false;
                }
            }
            return $reflect_method->invokeArgs($reflect_class,$real_vars);
        }else{
            api_msg("请求方法不存在!错误代码:107");
            return false;
        }
    }catch (\ReflectionException $e){
        api_msg('请求类不存在!错误代码:106');
        return false;
    }
}

/**
 * 调用api时的输出信息
 * 如果参数不为空则为设置信息，如果为空则时取信息
 * @param string $msg 信息
 * @return string 返回上次设置的信息
 */
function api_msg($msg=''){
    static $message;
    if(!empty($msg)){
        $message = $msg;
    }else{
        return $message;
    }
}

function is_api(){
    return APP_MODE == 'api';
}

/**
 * 调用api时的输出信息
 */
function api_code($cd=0){
    static $code;
    if(!empty($cd)){
        $code = $cd;
    }else{
        return $code?$code:"0";
    }
}

function api_page($page=null){
    static $pg;
    if(!empty($page)){
        $pg = $page;
    }else{
        return $pg?$pg:false;
    }
}

/**
 * api数据签名认证
 * @param $data
 * @param $key
 * @return string
 */
function api_auth_sign($data,$key){
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    $str = "";
    foreach($data  as $k=>$v){
        $str .="$k=$v&";
    }
    $str = substr($str,0,-1); //去除末尾&符号
    $sign = sha1($str.$key); //生成签名
    return $sign;
}

function page($page,$page_size,$result){
	$true_page=($page-1)*$page_size;
	$result=array_slice($result,$true_page,$page_size);
	return $result;
}
