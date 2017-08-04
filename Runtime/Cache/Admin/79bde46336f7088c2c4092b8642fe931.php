<?php if (!defined('THINK_PATH')) exit();?><div class="center">
    <span class="btn btn-app btn-sm btn-light no-hover">
        <span class="line-height-1 bigger-170 blue"> <?php echo ($info["user"]); ?> </span>
        <br>
        <span class="line-height-1 smaller-90"> 用户数 </span>
    </span>
    <span class="btn btn-app btn-sm btn-pink no-hover">
        <span class="line-height-1 bigger-170"> <?php echo ($info["document"]); ?> </span>
        <br>
        <span class="line-height-1 smaller-90"> 内容数量 </span>
    </span>

    <span class="btn btn-app btn-sm btn-success no-hover">
        <span class="line-height-1 bigger-170"> <?php echo ($info["category"]); ?> </span>
        <br>
        <span class="line-height-1 smaller-90"> 分类数量 </span>
    </span>

    <span class="btn btn-app btn-sm btn-yellow no-hover">
        <span class="line-height-1 bigger-170"> <?php echo ($info["model"]); ?> </span>
        <br>
        <span class="line-height-1 smaller-90"> 模型数量 </span>
    </span>

   <span class="btn btn-app btn-sm btn-primary no-hover">
        <span class="line-height-1 bigger-170"> <?php echo ($info["addons"]); ?> </span>
        <br>
        <span class="line-height-1 smaller-90"> 已装插件 </span>
    </span>

     <span class="btn btn-app btn-sm btn-light no-hover">
        <span class="line-height-1 bigger-170 blue"> <?php echo ($info["module"]); ?> </span>
        <br>
        <span class="line-height-1 smaller-90"> 已装模块 </span>
    </span>
</div>
<div class="space-6"></div>
<hr/>