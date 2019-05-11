<?php
namespace common\helpers;

use yii\web\UploadedFile;
use common\models\UploadValidate;

class File
{
	public function upload($data, $filename)
	{
		$model = new UploadValidate();
        $tmp = UploadedFile::getInstanceByName('avatar');
        $model->file = $tmp;
        if(!$model->validate()) {
            return false;
        }

        $url = Yii::$app->AliyunOss->upload($tmp, "avatar");
        return $url;
	}
}