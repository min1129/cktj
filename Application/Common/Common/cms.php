<?php
/**
 * Created by PhpStorm.
 * User: tiptimes
 * Date: 15-4-11
 * Time: 上午11:01
 */

/**
 * category 栏目url的组装
 * 组装后的url跳转到相应的二级页面
 * url组装
 * @param  array $list  栏目列表
 * @return array 结果
 */
function list_url($list){
    if(APP_MODE != 'api'){ //判断是否时api模式
        if(!isset($list['id'])){
            foreach($list as $k=>$v){
                $list[$k]['url'] = U('Home/Index/category?cate='.$v['symbol'].'&p=1');
            }
        }else{
            $list['url']= U('Home/Index/category?cate='.$list['symbol'].'&p=1');
        }
    }
    return $list;
}

/**
 * 内容页url组装
 * $fun为回调函数，回调函数的作用是
 * 为列表扩展处理接口
 * @param $list
 * @param mixed $fun 回调函数
 * @return mixed
 */
function content_url($list,$fun=null){
    if(!isset($list['id'])){//代表是二维数组
        foreach((array)$list as $k=>$v){
            if($v['cover']>0){//封面图片
                $list[$k]['cover_path'] = get_cover_path($v['cover']);
            }elseif($v['auto_image']>0){//自动提取图片
                $imagePath =  getImage($v['content'],$v['auto_image']);
                if($imagePath){
                    $list[$k]['cover_path'] = $imagePath;
                }
            }elseif(!empty($v['picture'])){//图片模型,解析图片模型的图片
                $picture = json_decode(htmlspecialchars_decode($list[$k]['picture']),true);
                $picture_arr = array();
                $flag = false;
                foreach($picture as $kk=> $item){
                    $list[$k]['cover_path'] = get_cover_path($item['id']);
                    $picture_arr[] = array('path' => get_cover_path($item['id']),"text"=>$item['text']);
                    if($item['cover']){
                        $flag = true;
                        $list[$k]['cover_path'] = get_cover_path($item['id']);
                    }
                }
                $list[$k]['picture'] = $picture_arr;

                if(!$flag && !empty($picture)){//默认第一张为封面
                    $list[$k]['cover_path'] = get_cover_path($picture[0]['id']);
                }
            }

            if(APP_MODE == 'api'){//content加数据
                $create_time =date('Y-m-d H:i',$list[$k]['create_time']);
                $html = <<<sql
<h3 style="text-align:center">{$list[$k]['title']}</h3><span>发布时间:{$create_time}</span>&nbsp;&nbsp;<hr/>
sql;
                $html .= htmlspecialchars_decode($list[$k]['content']);
                $list[$k]['content'] =$html;
            }

            if(!empty($v['link'])){ //外链
                if('http://' === substr($v['link'], 0, 7)){
                    $list[$k]['url'] = $v['link'];
                }else if('www' === substr($v['link'], 0, 3)){ //容错
                    $list[$k]['url'] = ('http://'.$v['link']);
                }else{
                    if(APP_MODE != 'api'){
                        $list[$k]['url'] = U($v['link']);
                    }else{
                        $list[$k]['url'] = 'function';
                    }
                }
            }else{
                if(APP_MODE != 'api'){
                    $cate = \Common\Api\CategoryApi::get_category($list[$k]['category_id'],'symbol');
                    $list[$k]['url'] = U('Home/Index/content?cate='.$cate.'&id='.$list[$k]['id']);
                }
            }
            if($fun instanceof Closure){ //回调接口
                $fun($list[$k]);
            }
        }
    }else{
        if($list['cover']>0){//封面图片
            $list['cover_path'] = get_cover_path($list['cover']);
        }elseif($list['auto_image']>0){
            $imagePath =  getImage($list['content'],$list['auto_image']);
            if($imagePath){
                $list['cover_path'] = $imagePath;
            }
        }elseif(!empty($list['picture'])){//图片模型
            $picture = json_decode(htmlspecialchars_decode($list['picture']),true);
            $flag = false;
            $picture_arr = array();
            foreach($picture as $item){
                $picture_arr[] = array('path' => get_cover_path($item['id']),"text"=>$item['text']);
                if($item['cover']){
                    $list['cover_path'] = get_cover_path($item['id']);
                    $flag = true;
                }
            }
            $list['picture'] = $picture_arr;
            if(!$flag && !empty($picture)){//默认第一张为封面
                $list['cover_path'] = get_cover_path($picture[0]['id']);
            }
        }

        if(!empty($list['link'])){ //外链
            if('http://' === substr($list['url'], 0, 7)){
                $list['url'] = $list['link'];
            }else if('www' === substr($list['link'], 0, 3)){
                $list['url'] = ('http://'.$list['link']);
            }else{
                if(APP_MODE != 'api'){
                    $list['url'] = U($list['link']);
                }else{
                    $list['url'] = "function";
                }
            }
        }else{
            if(APP_MODE != 'api'){
                $cate = \Common\Api\CategoryApi::get_category($list['category_id'],'symbol');
                $list['url'] = U('Home/Index/content?cate='.$cate.'&id='.$list['id']);
            }
        }
        if($fun instanceof Closure){ //回调函数
            $fun($list);
        }
    }
    return $list;
}

/**
 * 正则匹配
 * @param $value
 * @param $rule
 * @return bool
 */
function regex($value='',$rule='') {
    $validate = array(
        'require'   =>  array('rule'=>'/\S+/','name'=>'必填'),
        'email'     =>  array('rule'=>'/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/','name'=>'邮箱'),
        'url'       =>  array('rule'=>'/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/','name'=>'url'),
        'currency'  =>  array('rule'=>'/^\d+(\.\d+)?$/','name'=>'货币(正实数)'),
        'number'    =>  array('rule'=>'/^\d+$/','name'=>'数字') ,
        'zip'       =>  array('rule'=>'/^\d{6}$/','name'=>'长度为6的整数'),
        'integer'   =>  array('rule'=>'/^[-\+]?\d+$/','name'=>'带符号的整数'),
        'double'    =>  array('rule'=>'/^[-\+]?\d+(\.\d+)?$/','name'=>'带符号的实数'),
        'english'   =>  array('rule'=>'/^[A-Za-z]+$/','name'=>'纯英文'),
        'point'   =>  array('rule'=>'/^\d+\.\d+\,\d+\.\d+\$/','name'=>'坐标')
    );

    if(empty($value) && empty($rule)){
        return $validate;
    }

    // 检查是否有内置的正则表达式
    if(isset($validate[strtolower($rule)])){
        $rule       =   $validate[strtolower($rule)]['rule'];
    }
    return preg_match($rule,$value)===1;
}

/**
 * 链接解析
 * @param $list
 * @return mixed
 */
function link_url($list){
    if(!isset($list['url'])){//数组
        foreach($list as $k=>$v){
            if('http://' === substr($v['url'], 0, 7)){
                $list[$k]['url'] = $v['url'];
            }else if('www' === substr($v['url'], 0, 3)){
                $list[$k]['url'] = ('http://'.$v['url']);
            }else{
                if(APP_MODE != 'api'){
                    $list[$k]['url'] = U($v['url']);
                }else{
                    $list[$k]['url'] = $v['url']; //表明时功能节点,不是新闻
                }
            }

            if($list[$k]['picture_id']>0){
                $list[$k]['picture'] = get_cover_path($list[$k]['picture_id']);
            }else{
                $list[$k]['picture'] = C('TMPL_PARSE_STRING.__DEFAULT__');
            }
        }
    }else{
        if('www' === substr($list['url'], 0, 3)){
            $list['url'] = ('http://'.$list['url']);
        }else{
            if(APP_MODE != 'api'){
                $list['url'] = U($list['url']);
            }
        }
        if($list['picture_id]']>0){
            $list['picture'] = get_cover_path($list['picture_id]']);
        }else{
            $list['picture'] = C('TMPL_PARSE_STRING.__DEFAULT__');
        }
    }

    return $list;
}
/**********************************系统功能相关****************************************************/


/**********************************前台数据显示相关****************************************************/

/**
 * 获得栏目
 * 两个参数都不写则获得顶级栏目
 * 写了$cid可以选择是获得此分类的详细信息还是获得
 * 其子栏目信息
 * @param mixed $cid  栏目ID 或者栏目英文名称
 * @param bool $children 是否获得子分类
 * @param int $root_nav 当前分类树的根
 * @param string $activeClass 选中分类的样式
 * @return mixed
 */
function cat($cid='',$children=false,$root_nav=0,$activeClass='active'){
    if(empty($cid)){//获得顶级分类
        $result = \Common\Api\CategoryApi::get_top_category(array('index_show'=>1));
        foreach($result as $k =>$v){
            $result[$k]['has_child'] = \Common\Api\CategoryApi::get_children_count($v['id'],array('index_show'=>1)); //是否有子分类
            $result[$k]['class'] =($result[$k]['id']==$root_nav?$activeClass:''); //导航样式
        }
        return $result;
    }else{
        if($children){//获得指定分类的子分类
            return \Common\Api\CategoryApi::get_children_category($cid);

        }else{//获得指定分类
            return \Common\Api\CategoryApi::get_category($cid);
        }
    }
}

/**
 * 获得栏目特定字段
 * @param mixed $cid  栏目ID 或者栏目英文名称
 * @param string $field 字段
 * @return mixed
 */
function cat_field($cid,$field){
    $result = \Common\Api\CategoryApi::get_category($cid);
    return empty($field)?$result:$result[$field];
}

/**
 * 获得某栏目下的新闻
 * @param mixed $cid  栏目ID 或者栏目英文名称
 * @param string $limit 如果输入0,10类似则返回指定区间内的数据,如果是数字则返回指定页面,页面大小
 * 由栏目设置决定
 * @return bool|mixed
 */
function lists($cid,$limit='0,10'){
    if(is_numeric($cid)){
        $result = \Common\Api\DocumentApi::lists($cid,$limit);
        return $result;
    }else{
        return false;
    }
}

/**
 * 获得推荐位置信息
 * @param int $pos 推荐位id
 * @param mixed $limit 取的范围
 * @param mixed $category 栏目ID 或者栏目英文名称
 * @return bool|mixed
 */
function position($pos,$limit='0,5',$category=''){
    if(is_numeric($pos)){
        $result = \Common\Api\DocumentApi::position(3,$category,$limit);
        return $result;
    }else{
        return false;
    }
}

/**
 * 获取某个分组下的链接
 * @param string $group  分组名称
 * @param int $start  起始下标
 * @param  int$length 长度
 * @return array
 */
function get_link($group,$start,$length){
    $result = \Common\Api\LinkApi::get_link($group);
    if(isset($start)){
        return array_slice($result,$start,$length);
    }else{
        return $result;
    }
}