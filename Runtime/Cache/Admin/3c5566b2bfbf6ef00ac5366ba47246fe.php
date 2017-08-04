<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
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


    <link rel="stylesheet" href="/cktj/Public/Admin/css/am.min.css" />
    <style>
        #captcha-img{
            cursor: pointer;
            width: 100%;
            height: 55px;
            border: 1px #d5d5d5 solid;
        }
        #login_btn{
            font-size: 16px;
            width: 100%;
            height: 40px;
        }
    </style>
</head>
<body class="login-layout blur-login">
<!--[if lt IE 8]>
<div class="alert alert-block alert-danger fade in" style="margin-bottom: 0">您正在使用 <strong>过时的</strong> 浏览器. 是时候 <a target="_blank" href="http://browsehappy.com/">更换一个更好的浏览器</a> 来提升用户体验.</div>
<![endif]-->
<div class="main-container">
<div class="main-content ">
<div class="row">
<div class="col-sm-10 col-sm-offset-1">
<div class="login-container">
<div class="center">
    <h1>
        <i class="ace-icon fa fa-leaf green"></i>

        <span class="white" id="id-text2">仓库统计</span>
    </h1>
    <h4 class="blue" id="id-company-text">&copy;</h4>
</div>

<div class="space-6"></div>

<div class="position-relative">
    <div id="login-box" class="login-box visible widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header blue lighter bigger">
                    <i class="icon-coffee green"></i>

                </h4>

                <div class="space-6"></div>

                <form id="login_form" action="<?php echo U('login');?>" method="post">
                    <fieldset>
                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" class="form-control"
                                                                   name="username" id="username"
                                                                   placeholder="用户名"/>
															<i class="ace-icon fa fa-user"></i>
														</span>
                        </label>

                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="password" class="form-control"
                                                                   name="password" id="password"
                                                                   placeholder="密码"/>
															<i class="ace-icon fa fa-lock"></i>
														</span>
                        </label>
                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" maxlength="5" class="form-control"
                                                                   name="verify"
                                                                   placeholder="验证码"/>
															<i class="ace-icon fa fa-lock"></i>
														</span>
                        </label>
                        <label class="block clearfix">
                            <img alt="验证码" id="captcha-img" title="点击更新" src="<?php echo U('verify');?>">
                        </label>


                        <div class="red" id="error_tip">
                        </div>
                        <div class="space">

                        </div>
                        <div class="clearfix">
                            <Button id="login_btn" type="button" class="btn btn-sm btn-primary ">
                                登录
                            </Button>
                        </div>
                    </fieldset>
                </form>
            </div>
            <!-- /widget-main -->

            <!--<div class="toolbar clearfix">-->
                <!--<div>-->
                    <!--<a href="#" data-target="#forgot-box" class="forgot-password-link">-->
                        <!--<i class="icon-arrow-left"></i>-->
                        <!--忘记密码-->
                    <!--</a>-->
                <!--</div>-->

                <!--<div>-->
                    <!--<a href="#" data-target="#signup-box" class="user-signup-link">-->
                        <!--注册-->
                        <!--<i class="icon-arrow-right"></i>-->
                    <!--</a>-->
                <!--</div>-->
            <!--</div>-->
        </div>
        <!-- /widget-body -->
    </div>

    <div id="forgot-box" class="forgot-box widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header red lighter bigger">
                    <i class="icon-key"></i>
                    找回密码
                </h4>

                <div class="space-6"></div>
                <p>
                    请填写您的注册邮箱
                </p>

                <form>
                    <fieldset>
                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="email" id="password_email"  class="form-control" placeholder="Email" />
															<i class="icon-envelope"></i>
														</span>
                        </label>
                        <p id="back_error_tip" class="red"></p>
                        <div class="clearfix">
                            <button type="button" id="password_back" class="width-35 pull-right btn btn-sm btn-danger">
                                <i class="icon-lightbulb-o"></i>
                                <span class="bigger-110">确定</span>
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div><!-- /.widget-main -->

            <div class="toolbar center">
                <a href="#" data-target="#login-box" class="back-to-login-link">
                    返回
                    <i class="icon-arrow-right"></i>
                </a>
            </div>
        </div><!-- /.widget-body -->
    </div><!-- /.forgot-box -->

    <div id="signup-box" class="signup-box widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header green lighter bigger">
                    <i class="ace-icon fa fa-users blue"></i>
                    新用户注册
                </h4>

                <div class="space-6"></div>
                <p> 填写您的注册信息: </p>
                <form id="register_form">
                    <fieldset>
                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="email" required class="form-control" name="email" placeholder="邮箱" />
															<i class="icon-envelope"></i>
														</span>
                        </label>

                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" class="form-control" name="username" placeholder="用户名" />
															<i class="icon-user"></i>
														</span>
                        </label>
                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" class="form-control" name="nickname" placeholder="企业简称" />
															<i class="icon-user"></i>
														</span>
                        </label>


                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="password" class="form-control" name="password" placeholder="密码" />
															<i class="icon-lock"></i>
														</span>
                        </label>

                        <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="password" class="form-control" name="re_password" placeholder="确认密码" />
															<i class="icon-retweet"></i>
														</span>
                        </label>

                        <label class="block">
                            <input type="checkbox" class="ace" />
														<span class="lbl">
															我同意
															<a href="#">用户条款</a>
														</span>
                        </label>


                        <p id="register_error_tip" class="red"></p>

                        <div class="clearfix">
                            <button type="reset" class="width-30 pull-left btn btn-sm">
                                <i class="icon-refresh"></i>
                                <span class="bigger-110">重置</span>
                            </button>

                            <button type="button" id="register_button" class="width-65 pull-right btn btn-sm btn-success">
                                <span class="bigger-110">注册</span>
                                <i class="icon-arrow-right"></i>
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="toolbar center">
                <a href="#" data-target="#login-box" class="back-to-login-link">
                    <i class="icon-arrow-left"></i>
                    返回
                </a>
            </div>
        </div><!-- /.widget-body -->
    </div><!-- /.signup-box -->
</div><!-- /.position-relative -->

</div>
</div><!-- /.col -->
</div><!-- /.row -->
</div><!-- /.main-content -->
</div>
<!-- /.main-container -->

<!-- 底部 -->
<script  type="text/javascript">
    $(function(){

        //判断浏览器是否支持placeholder属性
        var supportPlaceholder='placeholder'in document.createElement('input'),

                placeholder=function(input){

                    var text = input.attr('placeholder'),
                            defaultValue = input.defaultValue;

                    if(!defaultValue){
                        input.val(text).addClass("phcolor");
                    }

                    input.focus(function(){
                        if(input.val() == text){
                            $(this).attr('type',$(this).data('type'));
                            $(this).val("");
                        }
                    });

                    input.blur(function(){
                        if(input.val() == ""){
                            $(this).attr('type','text');
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
                var type = $(this).attr("type");
                if(type == "text" || type == "password"){
                    if(type=='password'){
                        $(this).attr('type','text');
                        $(this).data('type','password');
                    }else{
                        $(this).data('type','text');
                    }
                    placeholder($(this));
                }
            });
        }

    });

    jQuery(function($) {
        $(document).on('click', '.toolbar a[data-target]', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('.widget-box.visible').removeClass('visible');//hide others
            $(target).addClass('visible');//show target
        });
    });

    var captcha_url = "<?php echo U('verify');?>";
    $("#captcha-img").prop("src", captcha_url);
    //更换验证码
    $("#captcha-img").click(function () {
        $(this).prop("src", captcha_url+"?random="+Math.random());
    });
    $("#login_btn").click(function(){
        $.post("<?php echo U('login');?>", $("#login_form").serialize(),function(data){
            if(data.status){
                location.href="<?php echo U('index/index');?>";
            }else{
                $("#error_tip").empty().text(data.msg);
                $("#captcha-img").click();
                shake('#error_tip');
            }
        },'json');
    });

    $("input[name='verify']").keyup(function(e) {
        if (e.keyCode === 13) {
            $("#login_btn").click();
            return false;
        }
    });


    $("#register_button").click(function(){
        $(this).addClass("disabled");
        $.post("<?php echo U('register');?>", $("#register_form").serialize(),function(data){
            $("#register_button").removeClass("disabled");
            if(data.status){
                $("#register_error_tip").css('color',"green").empty().text("注册成功!");
                setTimeout(function(){
                    location.href="<?php echo U('index/index');?>";
                },1000);
            }else{
                $("#register_error_tip").css('color',"red").empty().text(data.msg);
            }
            shake("#register_error_tip");
        },'json');
    });

    $("#password_back").click(function(){
        $(this).addClass("disabled");
        $.post("<?php echo U('getPasswordBack');?>", {"email":$("#password_email").val()},function(data){
            $("#password_back").removeClass("disabled");
            if(data.status){
                $("#back_error_tip").css('color',"green").empty().text(data.msg);
            }else{
                $("#back_error_tip").css('color',"red").empty().text(data.msg);
            }
            shake("#back_error_tip");
        },'json');
    });

    function shake(ele) {
        $(ele).addClass("shake");
        setTimeout(function() {
            $(ele).removeClass("shake");
        }, 1000);
    }
</script>
</body>
</html>