<?php
/**
 * 
 * @author quick
 *
 */
namespace Addons\LiuYan\Model;
use Think\Model;

/**
 * 分类模型
 */
class LiuyanModel extends Model{
    protected  $_auto = array(
        array('create_time',NOW_TIME,self::MODEL_INSERT),
    );

    protected $_validate = array(
        array('name', 'require','姓名必须填写',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
        array('content','1,300','内容不限在300个字符之间',self::MUST_VALIDATE,'length',self::MODEL_BOTH),
    );
}