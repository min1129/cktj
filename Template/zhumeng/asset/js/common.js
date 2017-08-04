/**
 * Created by tiptimes on 15/4/30.
 */
$(function(){
    //自适应屏幕
    $(window).bind('resize',function(){
        $(".page_content").css('min-height',($(window).height()-230)+"px");
        $(".page-content").css('min-height',($(window).height()-230)+"px");
    });
    $(".page_content").css('min-height',($(window).height()-230)+"px");
    $(".page-content").css('min-height',($(window).height()-230)+"px");
    $("#footer").show();
});
