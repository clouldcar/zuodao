<?php
namespace api\components;

use Yii;
use yii\base\Component;
use OSS\OssClient;

class AliyunOss extends Component
{
    public static $oss;

    public function __construct()
    {
        parent::__construct();
        $accessKeyId = Yii::$app->params['aliyun']['accessKeyId'];
        $accessKeySecret = Yii::$app->params['aliyun']['accessKeySecret'];
        $endpoint = Yii::$app->params['oss']['endPoint'];
        self::$oss = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
    }

    /**
     * 使用阿里云oss上传文件
     * @param $filename   本地文件信息
     * @param $type   文件分类
     * @param $filepath 文件在本地的绝对路径
     * @return string     上传文件地址
     */
    public function upload($fileName, $filePath, $type)
    {
        $res = '';
        //OSS文件名
        $object = $this->getFileName($fileName, $type);
        $bucket = Yii::$app->params['oss']['bucket'];
        //调用uploadFile方法把服务器文件上传到阿里云oss
        try{
            if (self::$oss->uploadFile($bucket, $object, $filePath)) {
                $res = Yii::$app->params['oss']['url'] . $object;
            }
        } catch(OssException $e) {
            return;
        }

        return $res;
    }

    private function getFileName($file, $type)
    {
        $str = 'abcdefghijkmnpqrstuvwxyz23456789';
        $name = substr(str_shuffle($str), 0, 10) . $this->getExt($file);
        if($type == 'avatar') {
            return 'avatar/' . $name;
        }

        if($type == 'article') {
            return 'article/' . $name;
        }
    }

    //获取后缀
    private function getExt($file) 
    {
        $tmp = explode('.', $file);

        return '.' . end($tmp);
    }

    public function test()
    {
        echo 123;
        echo "success";
    }
}