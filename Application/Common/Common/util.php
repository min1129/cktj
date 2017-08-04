<?php
/**
 * Created by PhpStorm.
 * User: tiptimes
 * Date: 15-4-11
 * Time: 上午11:32
 */

/**
 * 当前url做标记
 */
function MK(){
    trace($_SERVER['REQUEST_URI']);
    Cookie('__forward__',$_SERVER['REQUEST_URI']);
}

/**获取之前做标记的url
 * @return mixed|null
 */
function LK(){
    return Cookie('__forward__');
}

/**客户端重定向
 * @param $url
 */
function  JDIRedirect($url){
    header("location: ".$url);
    exit;
}

/**
 * 通用上传文件函数
 * @return array
 */
function upload_file(){
    $return  = array('status' => 1, 'msg' => '上传成功', 'data' => '');
    /* 调用文件上传组件上传文件 */
    $File = D('File');
    $file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
    $info = $File->upload(
        $_FILES,
        C('DOWNLOAD_UPLOAD'),
        C('DOWNLOAD_UPLOAD_DRIVER'),
        C("UPLOAD_{$file_driver}_CONFIG")
    );

    /* 记录附件信息 */
    if($info){
        $return['data'] = $info['download']['id'];
        $return['msg'] = $info['download']['name'];
    } else {
        $return['status'] = 0;
        $return['msg']   = $File->getError();
    }
    return $return;
}

/**
 *上传图片函数
 */
function upload_image(){
    //TODO: 用户登录检测
    /* 返回标准数据 */
    $return  = array('status' => 1, 'msg' => '上传成功', 'data' => '');
    /* 调用文件上传组件上传文件 */
    $Picture = D('Picture');
    $pic_driver = C('PICTURE_UPLOAD_DRIVER');
    $info = $Picture->upload(
        $_FILES,
        C('PICTURE_UPLOAD'),
        C('PICTURE_UPLOAD_DRIVER'),
        C("UPLOAD_{$pic_driver}_CONFIG")
    ); //TODO:上传到远程服务器

    if($info){
        $return['status'] = 1;
        if(APP_MODE =="api"){
            $return['data'] = $info;
        }else{
            $return = array_merge($info['download']?$info['download']:$info['file'], $return);
        }
    } else {
        $return['status'] = 0;
        $return['msg']   = $Picture->getError();
    }
    return $return;
}

/**
 * 发送通知
 * @param int $uid 通知用户
 * @param string $title 通知标题
 * @param string $detail 通知详情
 * @param int $flag 通知标识
 */
function notice($uid,$title,$bundle='',$flag=0){
    $data['uid'] = $uid;
    $data['title'] = $title;
    $data['bundle'] = $bundle;
    $data['flag'] = $flag;
    return D('Notice')->update($data);
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map  映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data, $map = array('status' => array
    (1 => '<span class="label label-success ">正常</span>',
        -1 => '<span class="label label-danger ">已删除</span>', 0 => '<span class="label label-danger ">禁用</span>',
        2 => '<span class="label label-warning">未审核</span>', 3 => '<span class="label">审核未通过</span>')))
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 列表转化成树形结构
 * @param $list
 * @param $pk
 * @param int $pid
 * @param string $child_key 子树key的名称
 * @param int $root
 * @param object $fun 回调函数
 * @return array
 */
function list_to_tree($list, $pk, $pid, $child_key ='children', $root = 0,$fun=null){
    $tree = array();
    if(is_array($list)){

        //主键引用
        $refer = array();
        foreach ($list as $key=>$value){
            $refer[$value[$pk]] = &$list[$key];
            if($fun instanceof Closure){ //回调接口
                $fun($list[$key]);
            }
        }
        foreach($list as $key=>$value){
            $parent_id = $value[$pid];
            if($parent_id == $root){
                $tree[] = &$list[$key];
            }else{

	            //父节点是否存在
                if(isset($refer[$parent_id])){
                    //加到父节点孩子集合中
                    $refer[$parent_id][$child_key][] = &$list[$key];
                }
            }
        }
        //return $refer;
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
    if(is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

/**
 * 返回树的先序遍历集合
 * @param array $tree 树
 * @param string $child_key 子树对应的key值
 * @param array $list 转换后的线性集合
 * @param int $level 当前遍历节点的嵌套等级
 * @param int $remove 要删除的节点
 * @author lihao <953445224@qq.com>
 */
function tree_to_list_first($tree,$child_key,&$list=array(),$level=0,$remove=0){
    if(is_array($tree)){
        foreach($tree as $key=>$value){
            if($remove == $value['id']){
                unset($tree[$key]);
                continue;
            }
            $value['level'] = $level;
            $list[]= $value;
            tree_to_list_first($value[$child_key],$child_key,$list,$level+1,$remove);
        }
    }
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str  要分割的字符串
 * @param  string $glue 分割符
 * @param  int $pos 获得特定的位置
 * @param int $default 默认的位置
 * @return array
 */
function str2arr($str, $glue = ',',$pos=null,$default=0){
    $str = trim($str," \t\n\r\0\x0B,");
    if(!is_null($pos)){
        $array =  explode($glue, $str);
        if(!empty($array[$pos])){
            return $array[$pos];
        }else{
            return $default;
        }
    }else{
        return explode($glue, $str);
    }
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array  $arr  要连接的数组
 * @param  string $glue 分割符
 * @return string
 */
function arr2str($arr, $glue = ','){
    return implode($glue, $arr);
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $suffix="",$charset="utf-8") {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return (mb_strlen($str,$charset)==mb_strlen($slice,$charset))?$slice:($suffix ? $slice.'...' : $slice);
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param string $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
    if(is_array($list)){
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ( $refer as $key=> $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 获得文件夹大小
 * @param string $path 文件夹路径
 * @return mixed
 */
function getDirectorySize($path)
{
    $totalsize = 0;
    $totalcount = 0;
    $dircount = 0;
    if ($handle = opendir ($path))
    {
        while (false !== ($file = readdir($handle)))
        {
            $nextpath = $path . '/' . $file;
            if ($file != '.' && $file != '..' && !is_link ($nextpath))
            {
                if (is_dir ($nextpath))
                {
                    $dircount++;
                    $result = getDirectorySize($nextpath);
                    $totalsize += $result['size'];
                    $totalcount += $result['count'];
                    $dircount += $result['dircount'];
                }
                elseif (is_file ($nextpath))
                {
                    $totalsize += filesize ($nextpath);
                    $totalcount++;
                }
            }
        }
    }
    closedir ($handle);
    $total['size'] = $totalsize;
    $total['count'] = $totalcount;
    $total['dircount'] = $dircount;
    return $total;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL,$format='Y-m-d H:i',$default='-'){
    if($time<=0){
        return $default;
    }
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * 人性化时间提示
 * @param int $time 时间戳
 * @return string
 */
function formatTime($time)
{
    $t = NOW_TIME - $time;
    $f = array(
        '31536000' => '年',
        '2592000' => '个月',
        '604800' => '星期',
        '86400' => '天',
        '3600' => '小时',
        '60' => '分钟',
        '1' => '秒'
    );
    if($t<30){
        return '刚刚';
    }
    foreach ($f as $k => $v) {

        if (0 != $c = floor($t / (int)$k)) {
            return $c.$v.'前';
        }
    }
}

/**
 * 图片剪裁
 * @param string $path 图片地址
 * @param int $width 剪裁后的宽
 * @param int $height 剪裁后的高
 * @param int $type 剪裁类型 详情见Image类
 * @return string
 */
function thumb($path, $width, $height, $type = 6){
    $root = false;
    if($width<=0 || $height<=0){
        return $path;
    }
    if(is_numeric($path)){
        $path = get_cover_path($path);
    }
    if(strpos($path,__ROOT__.'/')==0){//去除根目录
        $root = true;
        $path = substr($path,strlen(__ROOT__.'/'));
    }
    if(!is_file($path)){
        return "";
    }
    $imgInfo = pathinfo($path);
    $newImg = $imgInfo['dirname'].'/thum_'.$width.'_'.$height.'_'.$imgInfo["basename"];
    $newImgDir = $newImg;
    if(!is_file($newImgDir)){
        $image = new \Think\Image();
        $image->open($path);
        $image->thumb($width, $height,$type)->save($newImgDir);
    }
    if(!$newImg){
        return "";
    }
    if($root){ //还原根目录
        return __ROOT__.'/'.$newImg;
    }else{
        return $newImg;
    }
}

/**
 * 从编辑器内容中提取指定图片
 * @param string $content 内容
 * @param int $num 第N张图片
 * @return string 图片URL
 */
function getImage($content, $num = 1){
    $content = htmlspecialchars_decode($content);
    preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
    $num = $num -1;
    $img = $matches[1][$num];
    return $img;
}

/**
 * 获取文档封面图片
 * @param int $cover_id
 * @param string $field
 * @return mixed 完整的数据  或者  指定的$field字段值
 */
function get_cover($cover_id, $field = null){
    if(empty($cover_id)){
        return false;
    }
    $picture = M('Picture')->where(array('status'=>1))->getById($cover_id);
    return empty($field) ? $picture : $picture[$field];
}

/**
 * 获得完整的图片地址
 * @param $cover_id
 * @return bool|string
 */
function get_cover_path($cover_id){
    if(!$cover_id){
        return "";
    }
    return __ROOT__.get_cover($cover_id,'path');
}

/**
 * 是否为手机访问
 * @return bool
 */
function is_mobile(){
    if(session('tempMobile')){ //设置访问手机版
        return true;
    }else{
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if(strpos($agent,"NetFront") || strpos($agent,"iPhone") || strpos($agent,"MIDP-2.0") || strpos($agent,"Opera Mini") || strpos($agent,"UCWEB") || strpos($agent,"Android") || strpos($agent,"Windows CE") || strpos($agent,"SymbianOS")){
            return true;
        }else{
            return false;
        }
    }
}

/**
 * 模板路径
 * @return string
 */
function temp_path(){
    if(is_mobile()){
        return C('TEMP_PATH').'/'.C('MOBILE_THEME');
    }else{
        return C('TEMP_PATH').'/'.C('JDI_THEME');
    }
}

/**
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function jdi_send_mail($to, $name, $subject = '', $body = '', $attachment = null){
    $config = C('THINK_EMAIL');
    $mail             = new \Org\Util\PHPMailer(); //PHPMailer对象
    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();  // 设定使用SMTP服务
    $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    //   $mail->SMTPSecure = 'ssl';                 // 使用安全协议
    $mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
    $mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
    $mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
    $mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
    $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
    $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
    $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if(is_array($attachment)){ // 添加附件
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
 *给手机发送验证码
 *@param $to  要发送的手机
 *@param $datas  要发送的数据(数组)
 *@param $tempId  短信模版id
 *@return  发送成功返回true,失败返回false
 */
function send_sms($to,$datas,$tempId){
    //主帐号
    $accountSid= '8a48b551473976010147629ee8431226';

    //主帐号Token
    $accountToken= 'b162c78411454b99948096ca78a11c77';

    //应用Id
    $appId='8a48b5514ce46cb8014cee5d1671089c';

    //请求地址，格式如下，不需要写https://
    //$serverIP='sandboxapp.cloopen.com';开发环境
	
    $serverIP='app.cloopen.com';
    //请求端口
    $serverPort='8883';

    //REST版本号
    $softVersion='2013-12-26';

    $rest = new \Org\Util\CCPRestSDK($serverIP,$serverPort,$softVersion);
    $rest->setAccount($accountSid,$accountToken);
    $rest->setAppId($appId);

    $result = $rest->sendTemplateSMS($to,$datas,$tempId);
    if($result == NULL ) {
        // echo "result error!";
        return false;
    }
    if($result->statusCode!=0) {
        // echo "error code :" . $result->statusCode . "<br>";
        // echo "error msg :" . $result->statusMsg . "<br>";
        //TODO 添加错误处理逻辑
        return false;
    }else{
        // echo "Sendind TemplateSMS success!<br/>";
        // // 获取返回信息
        // $smsmessage = $result->TemplateSMS;
        // echo "dateCreated:".$smsmessage->dateCreated."<br/>";
        // echo "smsMessageSid:".$smsmessage->smsMessageSid."<br/>";
        //TODO 添加成功处理逻辑
        return true;
    }
}

/**
 * t函数用于过滤标签，输出没有html的干净的文本
 * @param string text 文本内容
 * @return string 处理后内容
 */
function op_t($text)
{
    $text = nl2br($text);
    $text = real_strip_tags($text);
    $text = addslashes($text);
    $text = trim($text);
    return $text;
}

/**
 * h函数用于过滤不安全的html标签，输出安全的html
 * @param string $text 待过滤的字符串
 * @param string $type 保留的标签格式
 * @return string 处理后内容
 */
function op_h($text, $type = 'html')
{
    // 无标签格式
    $text_tags = '';
    //只保留链接
    $link_tags = '<a>';
    //只保留图片
    $image_tags = '<img>';
    //只存在字体样式
    $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
    //标题摘要基本格式
    $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
    //兼容Form格式
    $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
    //内容等允许HTML的格式
    $html_tags = $base_tags . '<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
    //专题等全HTML格式
    $all_tags = $form_tags . $html_tags . '<!DOCTYPE><meta><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
    //过滤标签
    $text = real_strip_tags($text, ${$type . '_tags'});
    // 过滤攻击代码
    if ($type != 'all') {
        // 过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
    }
    return $text;
}

/**
 * 实体转换
 * @param $str
 * @param string $allowable_tags
 * @return string
 */
function real_strip_tags($str, $allowable_tags = "")
{
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    return strip_tags($str, $allowable_tags);
}

/**
 * 数字转换为大写
 * @param $num
 * @return mixed
 */
function n2n($num){
    static $ar = array("一","二","三","四","五");
    return $ar[$num];
}

function unicode_encode($uStr)
{
    $uStr = iconv('UTF-8', 'UCS-2', $uStr);
    $len = strlen($uStr);
    $str = '';
    //for ($i = 0; $i < $len – 1; $i = $i + 2){
    for ($i = 0; $i < $len - 1; $i = $i + 2) {
        $c = $uStr[$i];
        $c2 = $uStr[$i + 1];
        if (ord($c) > 0) { // 两个字节的文字
            $str .= '\u' . base_convert(ord($c), 10, 16) . base_convert(ord($c2), 10, 16);
        } else {
            $str .= $c2;
        }
    }
    return $str;
}

/**
 * excel 导出
 * @param $title
 * @param $data
 */
function exportExcel($title,$data){
    vendor("PHPExcel180.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,1, $title[0].' 时间:'.date('Y-m-d H:i:s'));
    $i=0;
    foreach($title[1] as $k=>$v){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i,2,$v);
        for($j=0; $j<count($data);$j++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i,3+$j,$data[$j][$k]);
        }
        $i++;
    }
    $objPHPExcel->getActiveSheet()->setTitle($title[0]);
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$title[0].'('.date('Ymd-His').').xls"');  //日期为文件名后缀
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式
    $objWriter->save('php://output');
    exit();
}

/**
 * excel 导出
 * @param $title
 * @param $data
 */
function exportTjExcel($title,$data,$tjTitle){
	vendor("PHPExcel180.PHPExcel");
	$objPHPExcel = new \PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,1, $title[0].' 时间:'.date('Y-m-d H:i:s'));
	$j=1;
	foreach($tjTitle as $k=>$v){
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($j,2,$k);
		$j=$j+3;
	}
	$i=0;
	foreach($title[1] as $k=>$v){
		if(substr($v,0,2)=='zw'){
			$name='发布岗位数量';
		}else if(substr($v,0,2)=='zh'){
			$name='发布账号数量';
		}else if(substr($v,0,3)=='num'){
			$name='招聘人数总和';
		}else{
			$name='区县';
		}
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i,3,$name);
		for($j=0; $j<count($data);$j++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i,4+$j,$data[$j][$k]);
		}
		$i++;
	}
	//exit;
	$objPHPExcel->getActiveSheet()->setTitle($title[0]);
	$objPHPExcel->setActiveSheetIndex(0);
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$title[0].'('.date('Ymd-His').').xls"');  //日期为文件名后缀
	header('Cache-Control: max-age=0');
	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式
	$objWriter->save('php://output');
	exit();
}


function get_temp_list(){
    $result = array();
    $dir = C('TEMP_PATH') . '/';
    $dir = trimPath($dir);
    $handler = opendir($dir);
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != "..") {
            if (is_dir($dir . $filename)) {
                $res = getDirectorySize($dir . $filename);
                $res['name'] = $filename;
                if(C('JDI_THEME')==$filename && C('MOBILE_THEME') == $filename){
                    $res['status']=3;
                }else{
                    if(C('JDI_THEME') == $filename){
                        $res['status'] = 1;
                    }elseif(C('MOBILE_THEME') == $filename){
                        $res['status'] = 2;
                    }else{
                        $res['status'] = 0;
                    }
                }
                $result[] = $res;
            }
        }
    }
    closedir($handler);
    return $result;
}

/**
 * 推送
 * @param $title
 * @param $content
 * @param $type
 * @param $data
 * @return bool|mixed
 */
function push($title,$content,$type,$data){
    $n_title   =  $title;
    $n_content =  $content;
    $sendno = 4;
    $receiver_value = '';
    $platform = 'android' ;
    $ex = array_merge(array('type'=>$type),$data);
    $msg_content = json_encode(array( 'n_title'=>$n_title, 'n_content'=>$n_content,'n_extras'=>$ex));
    $obj = new \Org\Util\jpush();
    return $obj->send($sendno, 4, $receiver_value, 1, $msg_content, $platform);
}

/**
 * 数据库map表快速读写
 * @param $key
 * @param null $value
 * @return bool|mixed
 */
function db_map($key,$value=null){
    $model = M('Map');
    $result = $model->where(array('key'=>$key))->find();
    if($value === null){
        return $result['value'];
    }else{
        if($result){ //更新
            $result['value'] = $value;
            return $model->save($result);
        }else{
            //新增
            $data['key'] = $key;
            $data['value'] = $value;
            return $model->add($data);
        }
    }
}
// 修正path
function trimPath($path) {
    $path = str_replace('\\', '/', $path);
    $path = str_replace('//', '/', $path);
    $path = str_replace('/./', '/', $path);
    return $path;
}

//循环删除目录和文件函数
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}