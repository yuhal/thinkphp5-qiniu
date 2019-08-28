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

/**
 * 二维数组根据某个键值排序
 * @param $arr
 * @param $keys
 * @param $type
 * @return array
 */
function arraySort($arr, $keys, $type = 'asc') {
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v){
        $keysvalue[$k] = $v[$keys];
    }
    $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
       $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 过反射函数转化为数组可以处理这样的问题
 * @param $object
 * @return array
 */
function object_to_array($object) {  
    $ref = new \ReflectionClass($object);
    $props = $ref->getProperties();
    $array = array();
    foreach ($props as $prop) {
        $prop->setAccessible(true);
        $array[$prop->getName()] = $prop->getValue($object);
        $prop->setAccessible(false);
    }
    return $array;
}

/**
 * 重构数组键名
 * @param $arr
 * @return array
 */
function arrayKeyAsc($arr){
    $new_array = array();
    if($arr){
        foreach ($arr as $key => $value) {
            $new_array[] = $value;
        }
    }
    return $new_array;
}