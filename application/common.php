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

function get_extension($file){
  return substr(strrchr($file, '.'), 1);
}