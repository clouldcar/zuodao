<?php
namespace api\controllers;

use Yii;
use yii\web\UploadedFile;
use common\helpers\Utils;
use common\models\UploadValidate;

/*附件*/
class AttachmentController extends BaseController
{
	public static function actionUploadAvatar()
	{
		$model = new UploadValidate();
		$tmp = UploadedFile::getInstanceByName('avatar');
		$model->file = $tmp;
		if($model->validate()) {
			$url = Yii::$app->AliyunOss->upload($tmp, "avatar");
			if($url)
			{
				return Utils::returnMsg(0, null, $url);
			}
			else
			{
				return Utils::returnMsg(1, $model->message);
			}
		}
	}
}