<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 15/1/27
 * Time: 下午3:27
 */

namespace Modules\AppClient\Api;
class AppClientApi {
    /**
     * 下载客户端
     * @param  int $type 1代表android 2代表ios
     * @return bool
     */
    static public  function downLoadApp($type=1){
        $result = M('AppClient')->where(array('type'=>$type))->order('create_time desc')->find();
        if($result){
            $File = D('File');
            $root = C('DOWNLOAD_UPLOAD.rootPath');
            M("AppClient")->where(array('id'=>$result['id']))->setInc("download_num");//下载次数加一
            if(false === $File->download($root, $result['file'])){
                //Todo 下载失败 下载次数减一
                return false;
            }
        }else{
            api_msg("未找到对应客户端!");
            return false;
        }
    }

    /**
     * 检查更新
     * @param int $type 1代表android 2代表ios
     * @return array
     */
    static public function checkUpdate($type){
        $result = M('AppClient')->where(array('type'=>$type))->field(true)->order('create_time desc')->find();
        if($result){
            return $result;
        }else{
            api_msg("数据不存在");
            return false;
        }
    }
} 