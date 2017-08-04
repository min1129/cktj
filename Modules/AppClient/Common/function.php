<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 15/1/28
 * Time: 下午3:33
 */

/**
 * 必须实现返回后台菜单节点
 * array(
 *   'index'=>array('title'=>'商品管理','action'=>'index,edit,add')
 * );
 */
function _MODULE_MENU(){
    return array(
        'SP'=>array(
            array('title'=>'SP','url'=>'index/index','children'=>
                array(
                    array('title'=>'ooo','url'=>'index/aa')
                )),
            array('title'=>'dd','url'=>'index/ding')
        )
    );
}