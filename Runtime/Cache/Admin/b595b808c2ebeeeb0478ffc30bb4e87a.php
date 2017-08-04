<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8" />
<title><?php echo ((isset($meta_title) && ($meta_title !== ""))?($meta_title):C('WEB_SITE_TITLE')); ?></title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="renderer" content="webkit">
<!-- basic styles -->
<link href="/cktj/Public/Vendor/ace/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="/cktj/Public/Vendor/font-awesome-4.3.0/css/font-awesome.min.css" />
<!-- page specific plugin styles -->
<link rel="stylesheet" href="/cktj/Public/Vendor/ace/css/jquery-ui-1.10.3.custom.min.css" />
<link rel="stylesheet" href="/cktj/Public/Vendor/ace/css/jquery.gritter.css" />
<!-- fonts -->
<!-- ace styles -->
<link rel="stylesheet" href="/cktj/Public/Vendor/ace/css/ace.min.css" />
<link rel="stylesheet" href="/cktj/Public/Vendor/ace/css/ace-rtl.min.css" />
<link rel="stylesheet" href="/cktj/Public/Vendor/ace/css/ace-skins.min.css" />
<link rel="stylesheet" href="/cktj/Public/Admin/css/comm.css" />
<!--[if lte IE 8]>
<link rel="stylesheet" href="/cktj/Public/Vendor/ace/css/ace-ie.min.css" />
<![endif]-->

<!--[if !IE]> -->
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery-2.0.3.min.js"></script>
<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery-1.10.2.min.js"></script>
<![endif]-->


    

    
</head>
<!--[if IE 8]> <body  class="navbar-fixed ie8"> <![endif]-->
<!--[if IE 9]> <body   class="navbar-fixed ie9"> <![endif]-->
<!--[if !IE]><!-->
<body class="navbar-fixed">
<!--<![endif]-->
<div class="shade" style="display:none"></div>
<!-- 头部 -->

<div class="navbar navbar-default navbar-fixed-top">
<script type="text/javascript">
    try {
        ace.settings.check('navbar', 'fixed')
    } catch (e) {
    }
</script>
<div class="container-fluid">
    <div class="navbar-header">
        <button class="navbar-toggle collapsed" type="button" id="nav_top_main_bt" data-toggle="collapse" data-target="nav_top_main">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div class="navbar-collapse collapse" id="nav_top_main">
        <ul class="nav navbar-nav">

            <li class="menu_hide" style="background-color: #fff;height:2px"></li>
            <!--<li><a href="<?php echo U('Home/Index/index');?>" target="_blank" class="jdi-brand"><?php echo (C("ADMIN_WEB_TITLE")); ?></a></li>-->
            <li ><a  href="<?php echo U('Index/index');?>" class="<?php echo ((isset($__INDEXCLASS__) && ($__INDEXCLASS__ !== ""))?($__INDEXCLASS__):''); ?>">首页</a></li>
            <?php if(is_array($__MENU__["main"])): $i = 0; $__LIST__ = $__MENU__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i; if(($menu["hide"]) == "0"): ?><li ><a  class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>" href="<?php echo U($menu['url']);?>"><?php echo ($menu["title"]); ?></a></li><?php endif; endforeach; endif; else: echo "" ;endif; ?>

        </ul>
        <ul class="user_menu_ul nav navbar-nav navbar-right ">
            <li >
                <a data-toggle="dropdown" style="line-height: 13px" href="#" class="dropdown-toggle">
                    <i class="fa fa-user" style="line-height: 13px;font-size: 23px"></i>
                </a>
                <ul class="user-menu  dropdown-menu">
                    <li>
                        <span>您好:<?php echo session('user_auth.nickname');?></span>
                    </li>
                    <li class="menu_hide menu_hide2">
                        <a href="<?php echo U('Index/updatePassword');?>">
                            修改密码
                        </a>
                    </li>
                    <li class="menu_hide menu_hide2">
                        <a href="<?php echo U('Index/updateNickname');?>">
                            修改昵称
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo U('Public/logout');?>">
                            退出
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<!-- /.ace-nav -->
</div>


<!-- /头部 -->

<!-- 主体 -->
<div class="main-container" id="main-container">
    <script type="text/javascript">
        try {
            ace.settings.check('main-container', 'fixed')
        } catch (e) {
        }
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>

        
    <style>
        .main-content{
            margin-left: 0;
        }
    </style>


        <div class="main-content">
            <!--[if lt IE 8]>
            <div class="alert alert-block alert-danger fade in" style="margin-bottom: 0">您正在使用 <strong>过时的</strong> 浏览器. 是时候 <a target="_blank" href="http://browsehappy.com/">更换一个更好的浏览器</a> 来提升用户体验.</div>
            <![endif]-->
            <div class="page-content">
                <div class="page-header">
                    <h1 class="page-header-title">
                        
                        
                    </h1>
                </div>
                <!-- /.page-header -->

                <div class="row">
                    <div class="col-xs-12">
                        
    <?php echo hook('AdminIndex');?>

                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.page-content -->
            </div>
            <!-- /.main-content -->
        </div>
    </div>
</div><!-- /.main-container -->



<!-- /主体 -->

<!-- 底部 -->

<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/typeahead-bs2.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/ace-extra.min.js"></script>
<!--[if lt IE 9]>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/html5shiv.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/respond.min.js"></script>
<![endif]-->

<!--[if lte IE 8]>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/excanvas.min.js"></script>
<![endif]-->
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/ace.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/ace-elements.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery.hotkeys.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery.easy-pie-chart.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery.sparkline.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/flot/jquery.flot.pie.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/bootbox.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/jquery.gritter.min.js"></script>
<script type="text/javascript" src="/cktj/Public/Vendor/ace/js/bootstrap-wysiwyg.min.js"></script>

<script type="text/javascript">
    $(function(){
        //判断浏览器是否支持placeholder属性
        supportPlaceholder='placeholder'in document.createElement('input'),

                placeholder=function(input){

                    var text = input.attr('placeholder'),
                            defaultValue = input.defaultValue;

                    if(!defaultValue){

                        input.val(text).addClass("phcolor");
                    }

                    input.focus(function(){

                        if(input.val() == text){

                            $(this).val("");
                        }
                    });


                    input.blur(function(){

                        if(input.val() == ""){

                            $(this).val(text).addClass("phcolor");
                        }
                    });

                    //输入的字符不为灰色
                    input.keydown(function(){

                        $(this).removeClass("phcolor");
                    });
                };

        //当浏览器不支持placeholder属性时，调用placeholder函数
        if(!supportPlaceholder){

            $('input').each(function(){

                text = $(this).attr("placeholder");

                if($(this).attr("type") == "text"){

                    placeholder($(this));
                }
            });
        }

    });
    (function(){
        var ThinkPHP = window.Think = {
            "ROOT"   : "/cktj", //当前网站地址
            "APP"    : "/cktj/index.php", //当前项目地址
            "PUBLIC" : "/cktj/Public", //项目公共目录地址
            "DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
            "MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
            "VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
        }
        $("#nav_top_main_bt").click(function(){
            $("#nav_top_main").toggle(200);
        })
    })();
</script>
<script type="text/javascript" src="/cktj/Public/Vendor/think.js"></script>
<script type="text/javascript" src="/cktj/Public/Admin/js/common.js"></script>





</body>
</html>