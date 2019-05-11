<?php
namespace common\helpers;

use Yii;

class Identicon
{
    private $instance = null;
    
    public function __construct()
    {
        $this->instance = new \Identicon\Identicon();
    }

    public function createUserImg($uid)
    {
        return $this->createImg("identicon_user_{$uid}.png", 512);
    }
    
    public function createImg($filename, $size = 64, $hexaColor = null)
    {
    	$tmp_file = '/tmp/' . $filename;
        $imgData = $this->getImageData($filename, $size, $hexaColor);
        file_put_contents($tmp_file, $imgData);
        //上传到阿里云OSS
        $url = Yii::$app->AliyunOss->upload($tmp_file, "avatar");
        //删除临时文件
        unlink($tmp_file);
        return $url;
    }
    
    public function getImageData($string, $size = 64, $hexaColor = null)
    {
        return $this->instance->getImageData($string, $size, $hexaColor);
    }


}