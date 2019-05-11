<?php
namespace api\controllers;

use Yii;
use yii\web\UploadedFile;
use common\helpers\Utils;
use common\models\UploadValidate;

/*附件*/
class AttachmentController extends BaseController
{
    private $type = ['avatar', 'article'];
    public static function actionUpload()
    {
        parent::checkPost();

        $type = Yii::$app->request->get('type');


        if(!in_array($type, $this->type))
        {
            return Utils::returnMsg(1, '类型错误');
        }

        $model = new UploadValidate();
        $tmp = UploadedFile::getInstanceByName('file');
        $model->file = $tmp;
        if($model->validate()) {
            $url = Yii::$app->AliyunOss->upload($tmp->name, $tmp->tempName, $type);
            if($url)
            {
                return Utils::returnMsg(0, null, ['image_url' => $url . '/0', 'size' => []]);
            }
            else
            {
                return Utils::returnMsg(1, $model->message);
            }
        }
    }


    
}