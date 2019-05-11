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

        $data = Yii::$app->request->post();


        // if(!in_array($data['type'], $this->type))
        // {
        //     return Utils::returnMsg(1, '类型qaj误');
        // }

        $model = new UploadValidate();
        $tmp = UploadedFile::getInstanceByName('img');
        $model->file = $tmp;
        if($model->validate()) {
            $url = Yii::$app->AliyunOss->upload($tmp->name, $tmp->tempName, $data['type']);
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