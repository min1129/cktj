$(function()
{
	
	$('.c_ctn').bind('mouseover',function()
	{
		$(this).addClass("bg3");
	});
	$('.c_ctn').bind('mouseleave',function()
	{
		$(this).removeClass("bg3");
	});
	$('.c_ctn').click(function()
	{
		var url=$(this).children('span').text();
		window.open(url);
	});
	$('.nav_menu span a').bind("mouseover",function()
	{
		$(this).parent().parent().addClass('bd_btom');
		$(this).parent().next('div').show();
	});
	$('.nav_menu li').bind("mouseleave",function()
	{
		$(this).removeClass('bd_btom');
		$(this).children('div').hide();
	});
    $('.main_feature li a').bind('mouseover',function()
	{
		$(this).children('img').addClass('swing animated');
	});
	$('.main_feature li a').bind('mouseleave',function()
	{
		$(this).children('img').removeClass('swing animated');
	});
	$('.show_download span').click(function()
	{
		$(this).parent().hide();
	});
    

	$('.download').click(function(){
		$('.dw_wall').css('height',$(window).height());
		$('.dw_wall').show();
	});
	$('.dw_wall,.wx_wall').click(function(){
		$(this).css('display','none');
	});
    
	$('.wx_gw').click(function(){
		$('.wx_wall').css('height',$(window).height());
		$('.wx_wall').show();
	});
});

