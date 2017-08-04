<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 15/1/10
 * Time: 下午5:25
 */

namespace Think\Template\TagLib;
use Think\Template\TagLib;

class C extends  TagLib{
    // 标签定义
    protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'nav'       =>  array('attr' => 'name,rootNav,activeClass', 'close' => 1), //获取导航
        'cat'       => array('attr'=>'who,field','close'=>0),
        'list'     =>array('attr'=>'who,name,limit','close'=>1),
        'hot'     =>array('attr'=>'who,name,num','close'=>1),
        'link'   =>array('attr'=>'who,name,limit','close'=>1),
        'linkgroup' =>array('attr'=>'who','close'=>0),
        'pos'   =>array('attr'=>'who,name,limit','close'=>1),
        'query'     =>  array('attr'=>'sql,result','close'=>0),
        'prev'  =>array('attr'=>'cur,result','close'=>0),
        'next' =>array('attr'=>'cur,result','close'=>0),
    );
    /* 导航列表 */
    public function _nav($tag, $content){
        $class  = empty($tag['class'])?'active':$tag['class'];
        $root   = empty($tag['root'])?0:$this->autoBuildVar($tag['root']);
        $parse = '<?php $__NAV__=cat(\'\',false,'.$root.',\''.$class.'\'); ?>';
        $parse .= '<volist name="__NAV__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    //导航信息
    public function _cat($tag, $content){
        $who =  empty($tag['who'])?'null':$tag['who'];
        $field = empty($tag['field'])?'name':$tag['field'];
        $parse = '<?php echo cat_field(\''.$who.'\',\''.$field.'\') ?>';
        return $parse;
    }

    //列表信息
    public function _list($tag, $content){
        $cid  =  empty($tag['who'])?'null':$tag['who'];
        $limit = empty($tag['limit'])?'0,6':$tag['limit'];
        $parse = '<?php $__LIST__=lists(\''.$cid.'\',\''.$limit.'\'); ?>';
        $parse .= '<volist name="__LIST__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    //热点信息
    public function _hot($tag, $content){
        $cid  = empty($tag['who'])?'':$tag['who'];
        $num = empty($tag['limit'])?8:$tag['limit'];
        $parse = '<?php $__HOTLIST__=api(\'Document/hot_list\',array(\'cate\'=>\''.$cid.'\',
        \'limit\'=>'.$num.')); ?>';
        $parse .= '<volist name="__HOTLIST__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    //链接组别名称
    public function _linkgroup($tag, $content){
        $group  = empty($tag['who'])?'null':$tag['who'];
        $parse = '<?php echo C(\'LINK_GROUP\'.'.$group.'\')?>';
        return $parse;
    }

    //外链
    public function _link($tag, $content){
        $group  = empty($tag['who'])?'null':$tag['who'];
        $num = empty($tag['limit'])?'0,8':$tag['limit'];
        $limit_array = str2arr($num);
        $start = $limit_array[0];
        $length = $limit_array[1];
        $parse = '<?php $__LINK__=get_link(\''.$group.'\','.$start.','.$length.');?>';
        $parse .= '<volist name="__LINK__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    //推荐位
    public function _pos($tag,$content){
        $pos  = empty($tag['who'])?'null':$tag['who'];
        $limit = empty($tag['limit'])?'0,8':$tag['limit'];
        $cate = empty($tag['cate'])?'null':$tag['cate'];
        $parse = '<?php $__POS__ = position('.$pos.','.$limit.','.$cate.')?>';
        $parse .= '<volist name="__POS__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }

    // sql查询
    public function _query($tag,$content) {
        $sql       =    $tag['sql'];
        $result    =    !empty($tag['result'])?$tag['result']:'result';
        $parseStr  =    '<?php $'.$result.' = M()->query("'.$sql.'");';
        $parseStr .=    'if($'.$result.'):?>'.$content;
        $parseStr .=    "<?php endif;?>";
        return $parseStr;
    }

    public  function  _prev($tag,$content){
        $info   = $this->autoBuildVar($tag['cur']);
        $result   = !empty($tag['result'])?$tag['result']:'prev';
        $parse = '<?php $'.$result.' = api(\'Document/prev\',array(\'id\'=>$info[\'id\'],\'category_id\'=>$info[\'category_id\']))?>';
        return $parse;
    }

    public  function  _next($tag,$content){
        $info   = $this->autoBuildVar($tag['cur']);
        $result   = !empty($tag['result'])?$tag['result']:'next';
        $parse = '<?php $'.$result.' = api(\'Document/next\',array(\'id\'=>$info[\'id\'],\'category_id\'=>$info[\'category_id\']))?>';
        return $parse;
    }
}
