<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

return array(
	'except'=>array(//配置在表单中的键名 ,这个会是config[title]
		'title'=>'不需要压缩文件名称!(正则匹配，或者数组)',//表单的文字
		'type'=>'text',		 //表单的类型：text、textarea、checkbox、radio、select等
		'value'=>'',			 //表单的默认值
	),
    'type'=>array(
        'title'=>'类型!',
        'type'=>'select',
        'option'=>array(
            '1'=>'压缩',
            '2'=>'压缩并且合并'
        ),
        'value'=>'1',
    ),
);
