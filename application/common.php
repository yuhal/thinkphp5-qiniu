<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function isImage($filename)
{
 $types = ['gif','GIT','jpeg','JPEG','png','PNG','bmp','BMP','jpg','JPG']; //定义检查的图片类型
 $ext = get_extension($filename);
  if(in_array($ext, $types)){
    return true;
  }else{
    return false;
  }
}

// 获取文件后缀
function get_extension($file){
  return substr(strrchr($file, '.'), 1);
}

/**
 * 使用in_array()对两个二维数组取差集
 *  - 去除$arr1 中 存在和$arr2相同的部分之后的内容
 * @param $arr1
 * @param $arr2
 * @return array
 */
function get_diff_array_by_filter($arr1,$arr2){
	try{
	    return array_filter($arr1,function($v) use ($arr2){
	        return !in_array($v,$arr2);
	    });
	}catch (\Exception $exception){
	    return $arr1;
	}
}