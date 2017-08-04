<?php
/**
 * Created by PhpStorm.
 * User: tiptimes
 * Date: 15-4-11
 * Time: 上午11:35
 */


/**
 * 获得当前用户的特定信息
 * @param string $field 获取用户字段
 * @return string
 */
function user_field($field){
    return \Common\Api\UserApi::user_field($field);
}

/**
 * 获取指定用户字段
 * @param $uid
 * @param $field
 * @return mixed
 */
function get_user_field($uid,$field=''){
    $result = \Common\Api\UserApi::get_user_field($uid,$field);
    return $result;
}

/**
 * 获得制定id的用户昵称
 * @param $uid
 * @return mixed
 */
function get_nickname($uid){
    return \Common\Api\UserApi::get_nickname($uid);
}

/**
 * 获得用户头像 不存在则返回默认头像
 * @param $uid
 * @return bool|mixed|string
 */
function get_user_image($uid){
    $result = get_user_field($uid,'head');
    return $result?get_cover_path($result):C('TMPL_PARSE_STRING.__DEFAULT_PERSON_IMAGE__');
}
