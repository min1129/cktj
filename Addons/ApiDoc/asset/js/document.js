+function($){

    /**
     * 内部全局变量
     * @type {Object}
     */
    var

        /**
         * 当前窗口 jQuery 对象
         * @type {Object}
         */
            $win = $(window),

        /**
         * 当前document jQuery 对象
         * @type {Object}
         */
            $doc = $(document),

        /**
         * 文档模型对象
         * 封装了文档模型相关元素，属性，和一系列文档操作方法
         * @type {Object}
         */
            Doc = {
            /**
             * 是否开启调试模式
             * 开启调试模式后不会缓存任何数据
             */
            "debug" : false,

            /**
             * 当前文档章节标识
             * 配合当前文档标识可以定位一篇文档
             */
            "name" : null,

            /**
             * 文档属性菜单对象
             * 当前文档属性菜单
             */
            "menu" : null,

            /**
             * 文章对象
             * 当前章节内容区域对象
             */
            "article" : null,

            /**
             * 文章标题对象
             * 当前章节标题
             */
            "title" : null,

            /**
             * 文章说明对象
             * 当前章节说明
             */
            "discription" : null,

            /**
             * 文章内容对象
             * 当前章节内容，解析后的内容直接放入该元素
             */
            "content" : null,

            /**
             * 文档初始化
             * 主要调用 thinktree 插件初始化当前文档目录
             */
            "init" : function(){
                var self      = this, timeout,
                    $loading  = $("#loading"),
                    $search   = $("#search"), //搜索框
                    $menu     = $("nav"), //文档目录菜单
                    $article  = $("article"), //文章对象
                    $header   = $article.children("header"), //文章标题区域对象
                    $copylink = $header.find(".copylink"); //复制URL链接

                /* 相关属性赋值 */
                this.menu        = $menu;
                this.article     = $article;
                this.title       = $header.children("h1");
                this.discription = $header.children(".discription");
                this.content     = $article.children("section.content");
                this.name        = $article.data("name");
                this.loading     = $loading;
                this.book        = $("title").data("book");
                this.apiParam   = '';
                this.method = null;
                $(".apiDoc").click(function(){
                    if($(this).hasClass('jdi-init-doc')){ //说明文档
                        $('#introduce').show();
                        $('#apiClass').hide();
                        $('#apiDetail').hide();
                    }else if($(this).hasClass('jdi-class-doc')){//类说明文档
                        var index = $(this).data('index').split(',');
                        var $class = JDO_DOCUMENT[index[0]]['apiClass'][index[1]];
                        $("#apiClass .title").empty().html($class.name);
                        $("#apiClass .jdi-package").empty().html("<h3>包名</h3>"+$class.package);
                        $("#apiClass .jdi-introduce").empty().html('<h3>说明</h3>'+$class.introduce);
                        $("#apiClass .jdi-author").empty().html("<h3>作者</h3>"+$class.author);
                        $('#introduce').hide();
                        $('#apiDetail').hide();
                        $('#apiClass').show();
                    }else{
                        var index = $(this).data('index').split(',');
                        var method = JDO_DOCUMENT[index[0]]['apiClass'][index[1]]['method'][index[2]];
                        $("#apiDetail .title").empty().html(method.name);
                        var params = '<h3>参数列表</h3><ul>';
                        var str_params = "";
                        if(method.param){
                            for(var i=0; i<method.param.length; i++){
                                params +='<li>';
                                params += ((i+1)+"."+method.param[i][1]+method.param[i][3]+", 类型:"+method.param[i][0]+", 说明:"+method.param[i][2]);
                                params += '</li>';
                                if(method.param[i][1]){
                                    if(i==method.param.length-1){
                                        str_params+=("\""+method.param[i][1].substring(1)+"\""+":\"\"");
                                    }else{
                                        str_params+=("\""+method.param[i][1].substring(1)+"\""+":\"\",");
                                    }
                                }
                            }
                        }else{
                            params +='<h4>无参数</h4>'
                        }

                        params += '</ul>'
                        $("#apiDetail .jdi-introduce").empty().html('<h3>说明</h3>'+method.introduce);
                        $("#apiDetail .jdi-params").empty().html(params);
                        $("#apiDetail .jdi-return").empty().html("<h3>返回值</h3>"+'类型:'+method.return[0]+",说明:"+method.return[1]);
                        $("#apiDetail .jdi-url").empty().html("<h3>访问地址</h3>"+method.url);

                        if(method.testCache){
                            $("#test-url").val(method.testCache.url);
                            $("#test-param").val(method.testCache.param);
                            $("#test-result").html(method.testCache.result);
                        }else{
                            $("#test-url").val(host_url+method.url);
                            $("#test-param").val(str_params);
                            $("#test-result").empty();
                        }
                        Article.method = method;
                        $('#introduce').hide();
                        $('#apiClass').hide();
                        $('#apiDetail').show();
                    }
                });

                $("#test-button").click(function(){
                    var url = $("#test-url").val();
                    var pm = $("#test-param").val();
                    var param = "{"+pm+"}";
                    var o = JSON.parse(param);

                    var request = "";
                    if(pm){
                        for(var p in o){
                            request += (p+"="+o[p]+"&");
                        }
                    }
                    var strs = url.split("?");
                    if(strs.length !== 2){
                        alert("url格式不正确!");
                        return;
                    }
                    request +=  strs[1];
                    var timestamp =  parseInt(new Date().getTime()/1000);
                    request += ('&_time='+timestamp);//时间戳
                    var hash = hex_sha1(request+api_key);
                    request += ('&_hash='+hash);//数据签名
                    var after = "&_time="+timestamp+"&_hash="+hash;
                    $.post(url+after,o,function(data){
                        var result = ProcessObject(data, 0, false, false, false);
                        console.info(result);
                        $("#test-result").html("<PRE class='CodeContainer'>"+result+"</PRE>");
                        Article.method.testCache = {'url':url,'param':pm,'result':result};
                        var blinkCount = 0;
                        var timer = setInterval(function(){
                            if ($("#test-result").hasClass('test-black')) {
                                $("#test-result").removeClass('test-black').addClass('test-red');
                            }else{
                                $("#test-result").removeClass('test-red').addClass('test-black');
                            }
                            blinkCount++;
                            if(blinkCount == 10){
                                clearInterval(timer);
                            }
                        },100);
                    },'json');
                });


                /* 初始化树形菜单，完成后高亮显示当前菜单 */
                $menu.thinktree();
                //高亮显示当前菜单
                this.active();
                //绑定异步读取事件
                $menu.on("click", "li > div > a", function(){
                    //记录当前页面
                    window.location.hash = $(this).closest("li").data("name");
                    return false;
                });

                /* hash改变时加载新内容 */
                $win.on("hashchange", function(){
                    var name = window.location.hash.substr(1);
                    name && self.load(name);
                }).trigger("hashchange");

                /* 高亮代码 */
                this.prettify();


                /* 快捷键的支持 */
                $doc.thinkkeyboard({
                    /* 上一页 */
                    "left" : function(event){
                        var target = event.target.nodeName.toLowerCase();
                        if(target != "input" && target != "textarea"){
                            self.prev();
                            return false;
                        }
                    },

                    /* 下一页 */
                    "right" : function(event){
                        var target = event.target.nodeName.toLowerCase();
                        if(target != "input" && target != "textarea"){
                            self.next();
                            return false;
                        }
                    },

                    /* 全屏阅读 */
                    "ctrl+alt+f" : function(){
                        self.article.parent().toggleClass("fullscreen");
                        return false;
                    }
                });
            },

            /**
             * 上一页
             */
            "prev" : function(){
                var index = this.menu.find("li").index(this.menu.find("li.active"));

                //正在加载
                if(this.loading.is(":visible")) return;
                this.menu.find("li").eq(index - 1).children("div").find("a").click();
            },

            /**
             * 下一页
             */
            "next" : function(){
                var index = this.menu.find("li").index(this.menu.find("li.active")),
                    $item = this.menu.find("li").eq(index + 1);

                //正在加载
                if(this.loading.is(":visible")) return;

                /* 到达最后一个元素时调回到第一个 */
                if(!$item.length){
                    $item = this.menu.find("li").eq(0);
                }
                $item.children("div").find("a").click();
            },

            /**
             * 高亮显示当前节点
             * @param  {String} name 要高亮的节点名称
             * @return {Object}      当前文档对象
             */
            "active" : function(name){
                var name = name || this.name, $menu = this.menu, $item, title;

                //没有指定章节时选中第一章节
                $item = name ? $menu.find("li[data-name=" + name + "]") : $menu.find("li:first");
                $menu.data("ThinkTree").active($item);
                return this;
            },

            /**
             * 高亮代码
             * 调用prettify插件高亮内容中的代码
             * @return {Object}      当前文档对象
             */
            "prettify" : function(){
                var $code = this.content.find("pre");
                $code.each(function(){
                    var $this = $(this), $code = $this.children("code"), lang = $code.attr("class");
                    $this.addClass("prettyprint linenums").data("code", $code.text());
                    lang && $this.addClass("lang-" + lang);
                    /* 解决chrome浏览器数字不居中的问题 */
                    navigator.userAgent.match(/AppleWebKit/ig) && $this.addClass("webkit");
                });

                //调用高亮插件，高亮代码
                prettyPrint();

                //复制代码
                $code.append("<div class=\"copycode hover\"><a href=\"javascript:;\">复制代码</a></div>");
                /* 复制代码 */
                $code.find(".copycode a").zclip({
                    "path"      : this.article.data("zclip") + "/ZeroClipboard.swf",
                    "copy"      : function(){return $(this).closest("pre").data("code")},
                    "afterCopy" : function(){}
                }).parent().removeClass("hover");

                return this;
            },

            /**
             * 加载当前页面
             * @param  {Object} name 当前文档名称
             * @return {Object}      当前文档对象
             */
            "load" : function(name){
//                var self = this, $item, data;
//
//                /* Ajax获取页面数据并渲染 */
//                if(name != this.article.data("name")){ //非当前页则加载数据
//                    $item = $("li[data-name=" + name + "]");
//                    if(!self.debug && (data = $item.data("article"))){//读取缓存内容
//                        success(data); //渲染数据
//                    } else {
//                        $.get($item.children("div").find("a").attr("href"), function(data){
//                            success(data); //渲染数据
//                            $item.data("article", data); //缓存数据
//                        }, "json");
//                    }
//                    //高亮当前节点
//                    self.menu.data("ThinkTree").active($item);
//                    //设置网页标题
//                    $("title").text($item.children("div").text() + " - " + this.book);
//                }
//
//                return this;
//
//                /**
//                 * 渲染页面数据
//                 * @param  {Object} data 当前页面json数据对象
//                 */
//                function success(data){
//                    if(data.title && data.content){
//                        self.article.data("name", data.name);
//                        self.title.html(data.title);
//                        self.content.html(data.content);
//                        self.prettify(); //代码高亮
//                        //设置滚动条的位置
//                        self.article.parent().scrollTop(0);
//                    }
//                }
            }
        };

    /* 设置全局访问变量 */
    window.Article = Doc;

}(jQuery);
