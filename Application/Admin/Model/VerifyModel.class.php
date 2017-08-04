<?php
namespace Admin\Model;
use Think\Model;

class VerifyModel extends Model{
    protected  $_auto = array(
      array('create_time',NOW_TIME,self::MODEL_INSERT),
      array('update_time',NOW_TIME,self::MODEL_BOTH)
    );

    public function update(){
        $data = $this->create();
        if(!$data){
            return false;
        }
        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
        }
        return $res;
    }
}
