<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Common\Api;
/**
 * Class SystemApi
 * @package Common\Api
 * @author lh
 * @time 2015-03-07 09:57:37
 */
class SystemApi {
    /**
     * 图片上传
     */
    public static function upLoadImage(){
        if(UID <=0){
            api_msg("用户未登录");
            return false;
        }
        $result = upload_image();
        if($result['status']==0){
            api_msg($result['msg']);
            return false;
        }else{
            return $result['data'];
        }
    }

    /**
     * 获得图片 不压缩
     * @param int $id 图片id
     * @return bool|string
     */
    public  static  function  getPicture($id){
        return  get_cover_path($id);
    }
}