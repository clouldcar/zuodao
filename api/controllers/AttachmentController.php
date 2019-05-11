<?php
namespace api\controllers;

use Yii;
use yii\web\UploadedFile;
use common\helpers\Utils;
use common\models\UploadValidate;

/*附件*/
class AttachmentController extends BaseController
{
    private $type = ['avatar', 'img'];
    public static function actionUpload()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();


        if(!in_array($data['type'], $this->type))
        {
            return Utils::returnMsg(1, '类型错误');
        }

        $model = new UploadValidate();
        $tmp = UploadedFile::getInstanceByName($data['type']);
        $model->file = $tmp;
        if($model->validate()) {
            $url = Yii::$app->AliyunOss->upload($tmp, $data['type']);
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