//dom加载完成后执行的js
$(function(){
    $(".winopen").click(function(){
        var url = $(this).data('url');
        var height = $(this).data('height');
        var width = $(this).data('width');
        winopen(url,width?width:500,height?height:600);
    });
    //全选的实现
    $(".check-all").click(function(){
        $(".ids").prop("checked", this.checked);
    });
    $(".ids").click(function(){
        var option = $(".ids");
        option.each(function(i){
            if(!this.checked){
                $(".check-all").prop("checked", false);
                return false;
            }else{
                $(".check-all").prop("checked", true);
            }
        });
    });

    //ajax get请求
    $('.ajax-get').click(function(){
        var that = this;
        if ( $(that).hasClass('confirm') ) {
            cf($(that).data('tip'), function(re){
                if(re){
                    ajax_get(that);
                }
            });
        }else{
            ajax_get(that);
        }
        return false;
    });
    //ajax post submit请求
    $('.ajax-post').click(function(){
        var that = this;
        if ( $(that).hasClass('confirm') ) {
            cf($(that).data('tip'), function(re){
                if(re){
                    ajax_post(that);
                }
            });
        }else{
            ajax_post(that);
        }
        return false;
    });
});

function ajax_post(that){
    var target,query,form;
    var target_form = $(that).attr('target-form');
    if($(that).attr('validate')){
        if(!validateForm('.'+target_form)){
            return;
        }
    }
    if(($(that).attr('type')=='submit') || (target = $(that).attr('href')) || (target = $(that).attr('url')) ){
        var wait = $(that).data('wait');
        showLoading(wait);
        $(that).addClass('disabled');
        form = $('.'+target_form); //得到表单
        if (form.get(0)==undefined){
            removeLoading();
            $(that).removeClass('disabled');
            errorAlert("请选择要操作的数据！");
            return false;
        }else if ( form.get(0).nodeName=='FORM' ){ //form表单
            if($(that).attr('url') !== undefined){
                target = $(that).attr('url');
            }else{
                target = form.get(0).action;
            }
            query = form.serialize();
        }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
            query = form.serialize();
        }else{
            query = form.find('input,select,textarea').serialize();
        }
        var editDiv = $('div[contenteditable=true]'); //自定义编辑器
        for(var i=0; i<editDiv.length;i++){
            var html = $(editDiv[i]).html();
            var name = $(editDiv[i]).data('name');
            if(name){
                query += ("&"+name+"="+encodeURIComponent(html));
            }
        }

        var cityFiled = $('.city_field'); //地区
        for(var i=0; i<cityFiled.length; i++){
            var name =  $(cityFiled[i]).data('name');
            var valueArray = new Array();
            var select = $(cityFiled[i]).find('select');

            for(var j=0; j<select.length; j++){
                var value = $(select.get(j)).val();
                if(value==0){
                    removeLoading();
                    $(that).removeClass('disabled');
                    errorAlert("请完善地址信息！");
                    return;
                }
                if(value !== null){
                    valueArray.push(value);
                }
            }
            query += ("&"+name+"="+valueArray.join(','));
        }

        $.post(target,query).success(function(data){
            removeLoading();
            if (data.status==1) {
                if (data.url) {
                    okAlert(data.msg + ' 页面即将自动跳转~');
                }else{
                    okAlert(data.msg);
                }
                setTimeout(function(){
                    if (data.url) {
                        $(that).removeClass('disabled');
                        location.href=data.url;
                    }else if( $(that).hasClass('no-refresh')){
                        $(that).removeClass('disabled');
                        removeAlert();
                    }else{
                        $(that).removeClass('disabled');
                        location.reload();
                    }
                },1500);
            }else{
                errorAlert(data.msg);
                setTimeout(function(){
                    if (data.url) {
                        $(that).removeClass('disabled');
                        location.href=data.url;
                    }else{
                        $(that).removeClass('disabled');
                        removeAlert();
                    }
                },1500);
            }
        });
    }
}

/**
 * 通用ajax get方法
 * 一般用于单点操作
 * @param that
 */
function ajax_get(that){
    var target;
    if ( (target = $(that).attr('href')) || (target = $(that).attr('url')) ) {
        $(that).addClass('disabled');
        var wait = $(that).data('wait');
        $.get(target).success(function(data){
            removeLoading();
            if (data.status==1) {
                if (data.url) {
                    okAlert(data.msg + ' 页面即将自动跳转~');
                }else{
                    okAlert(data.msg);
                }
                setTimeout(function(){
                    if (data.url) {
                        $(that).removeClass('disabled');
                        location.href=data.url;
                    }else if( $(that).hasClass('no-refresh')){
                        $(that).removeClass('disabled');
                        removeAlert();
                    }else{
                        $(that).removeClass('disabled');
                        location.reload();
                    }
                },1500);
            }else{
                errorAlert(data.msg);
                setTimeout(function(){
                    if (data.url) {
                        location.href=data.url;
                        $(that).removeClass('disabled');
                    }else{
                        removeAlert();
                        $(that).removeClass('disabled');
                    }
                },1500);
            }
        });

    }
}

function errorAlert(text,title,time){
    title = (title===undefined?'':title);
    updateAlert(text,title,'gritter-error',time);
}

function okAlert(text,title,time){
    title = (title===undefined?'':title);
    updateAlert(text,title,'gritter-success',time);
}
function infoAlert(text,title,time){
    title = (title===undefined?'':title);
    updateAlert(text,title,'gritter-info',time);
}

function updateAlert(text , title, style,time){
    $.gritter.add({
        title: title,
        text: text,
        class_name: style+' gritter-center',
        time:time?time:4000
    });
}

function removeAlert(){
    $.gritter.removeAll();
}

function showLoading(wait){
   // $('.modal').modal('hide');
    wait=(wait ===undefined?'':wait);
    $('.page-content').append('<div class="message-loading-overlay"><i style="top: 50%" class="icon-spin icon-spinner orange2 bigger-200"></i><div class="overlay-tip">'+wait+'</div></div>');
}

function changeLoadingTip(str){
    $('.message-loading-overlay .overlay-tip').empty.html(str);
}

function removeLoading(){
    $('.page-content').find('.message-loading-overlay').remove();
}

function showProgress(tip,type){
    //$('.modal').modal('hide');
    var progress = '';
    if(type===0){//没有进度
        progress='<i style="top: 50%" class="icon-spin icon-spinner orange2 bigger-200"></i>';
    }else{
        progress='<div class="progress progress-striped"> <div class="progress-bar progress-bar-purple" style="width: 1%"></div> </div>';
    }
    tip=(tip ===undefined?'':tip);
    $('body').append('<div class="message-loading-overlay progress-bg">' +
        '<div class="progress-overlay ">'+progress+'</div>' +
        '<div class="overlay-tip">' +tip+
        '</div>'+
        '</div>');
}

function changeProgress(rate,str){
    if(str){
        $('.progress-bar').css('width','100%');
        $('.message-loading-overlay .overlay-tip').empty().html(str);
    }else{
        $('.progress-bar').css('width',rate+'%');
    }
}
function removeProgress(){
    $('body').find('.message-loading-overlay').remove();
}

function cf(tip,func){
    bootbox.confirm(tip===undefined?'确定执行此操作么?':tip,func);
}


function shake(ele) {
    $(ele).addClass("shake");
    setTimeout(function() {
        $(ele).removeClass("shake");
    }, 1000);
}


/*打开弹出窗口*/
function winopen(url, w, h) {
    $("html,body").css("overflow", "hidden");
    $("div.shade").show();
    var _body = $("body").eq(0);
    if ($("#dialog").length == 0) {
        if (!is_mobile()) {
            _body.append("<div id=\"dialog\"><iframe src='" + url + "' style='width:" + w + "px;height:100%' scrolling='auto' ></iframe></div>");
            $("#dialog").css({
                width : w,
                height : h,
                position : "fixed",
                "z-index" : "2000",
                top : ($(window).height() / 2 - h / 2),
                left : (_body.width() / 2 - w / 2),
                "background-color" : "#ffffff"
            });
        } else {
            $("div.shade").css("width", _body.width());
            _body.append("<div id=\"dialog\"><iframe src='" + url + "' style='width:100%;height:100%' scrolling='auto' ></iframe></div>");
            $("#dialog").css({
                width : _body.width(),
                height : h,
                position : "fixed",
                "z-index" : "2000",
                top : 0,
                left : 0,
                "background-color" : "#ffffff"
            });
        }
    } else {
        $("#dialog").show();
    }
}
/* 关闭弹出窗口*/
function myclose() {
    parent.winclose();
}

function winclose() {
    $("html,body").css("overflow", "auto");
    $("div.shade").hide();
    $("#dialog").html("");
    $("#dialog").remove();
}

function is_mobile(){
    return false;
}

function multiInputDatas(isObject){
    this.map    =  new Object();
    this.length = 0;
    this.isObject = isObject;

    this.setField = function(field,value){
        for(var each in this.map)
        {
            this.map[each][field]= value;
        }
    }

    this.size = function(){
        return this.length;
    }

    this.join = function(str){
        if(str === undefined){
            str = ',';
        }

        var array = new Array();

        for(var each in this.map)
        {
            array.push(this.map[each]);
        }
        if(isObject){
            return JSON.stringify(array);
        }else{
            return array.join(str);
        }
    }

    this.put = function(key, value){

        if( !this.map['_' + key])
        {
            ++this.length;
        }

        this.map['_' + key] = value;

    }

    this.remove = function(key){

        if(this.map['_' + key])
        {

            --this.length;
            return delete this.map['_' + key];
        }
        else
        {
            return false;
        }
    }

    this.containsKey = function(key){

        return this.map['_' + key] ? true:false;

    }

    this.get = function(key){

        return this.map['_' + key] ? this.map['_' + key]:null;

    }


    this.inspect=function(){
        var str = '';

        for(var each in this.map)
        {
            str+= '\n'+ each + '  Value:'+ this.map[each];
        }

        return str;
    }

    return this;
}