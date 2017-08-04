<?php
return array(
    /* 模块相关配置 */
    'DEFAULT_MODULE'     => 'Admin',
    'AUTOLOAD_NAMESPACE' => array('Addons' => JDICMS_ADDON_PATH,'Modules'=>JDICMS_MOUDLE_PATH), //扩展模块列表

    'TMPL_PARSE_STRING' => array(
        '__UPLOADS__' => __ROOT__ . '/Uploads',
        '__DEFAULT__'=>__ROOT__.'/public/Home/default.jpeg', //默认显示的图片
        '__VENDOR__' =>__ROOT__ . '/Public/Vendor',
	'__DEFAULT_PERSON_IMAGE__'=>__ROOT__.'/Public/Home/touxiang.png'
    ),
    'TAGLIB_PRE_LOAD' => 'c', //加载自定义标签库

//    'APP_SUB_DOMAIN_DEPLOY'   =>    1, // 开启子域名或者IP配置
//    'APP_SUB_DOMAIN_RULES'    =>    array(
//        'admin.nb.com'  => 'Admin',  // admin.domain1.com域名指向Admin模块
//        'api.nb.com'   => 'Api',  // test.domain2.com域名指向Test模块
//        'www.nb.com' =>'Home'
//    ),

    'COOKIE_EXPIRE'=>3600*24*30, //cooike 保存时间1个月
    /* 系统数据加密设置 */
    'UID_KEY' =>'xr%3Ci>[L?u2b}asdmscdosnR0"sXzoR0&AM^UjJe',//uid加密密钥,默认
    'DATA_AUTH_KEY' => 'xr%3Ci>[L?u2b}7;p~ED1hmWN"sXzoR0&AM^UjJe', //默认数据加密KEY
    'URL_KEY' =>'xr%3Ci>[L?u2b}sXzoR0"sXzoR0&AM^UjJe',//默认URL加密密钥
    'URL_MODEL'=>'1',
    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID
    // 添加数据库配置信息
    'DB_TYPE' => 'mysqli', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'cktj', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'root', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'jdi_', // 数据库表前缀
    /*系统加密密钥*/
    'ENCRYPT_KEY' => 'axdsvypo34da1',

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'jdi', // 缓存前缀
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型

    /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 10 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg,zip,apk,rar,tar,gz,7z,doc,docx,txt,xml,model', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/file/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //下载模型上传配置（文件上传类配置）

    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' =>10 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）

    /* 图片上传相关配置 */
    'VIDEO_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' =>10 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => ".flv,.swf,.mkv,.avi,.rm,.rmvb,.mpeg,.mpg,.ogg,.ogv,.mov,.wmv,.mp4,.webm,.mp3,.wav,.mid", //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）

    'PICTURE_UPLOAD_DRIVER' => 'local',
    //本地上传文件驱动配置
    'UPLOAD_LOCAL_CONFIG' => array(),
    'UPLOAD_BCS_CONFIG' => array(
        'AccessKey' => '',
        'SecretKey' => '',
        'bucket' => '',
        'rename' => false
    ),

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'jdi_', //session前缀
    'COOKIE_PREFIX'  => 'jdi_', // Cookie前缀 避免冲突
    'VAR_SESSION_ID' => 'session_id',	//修复uploadify插件无法传递session_id的bug

    //评论也每页显示数量
    'COMMENT_LIST'=>10,

    //邮件配置
    'THINK_EMAIL' => array(
        'SMTP_HOST'   => 'smtp.qq.com', //SMTP服务器
        'SMTP_PORT'   => '25', //SMTP服务器端口
        'SMTP_USER'   => '953445224@qq.com', //SMTP服务器用户名
        'SMTP_PASS'   => 'lihao2656360', //SMTP服务器密码
        'FROM_EMAIL'  => '953445224@qq.com', //发件人EMAIL
        'FROM_NAME'   => '佰邦科技', //发件人名称
        'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
        'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
    ),

);
