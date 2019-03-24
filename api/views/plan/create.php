<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model api\models\Plan */
/* @var $form ActiveForm */
?>
<div class="plan-create">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title') ?>
        <?= $form->field($model, 'team_id') ?>

        <?=$form->field($model2,'type')->checkboxList(['2'=>'事业','1'=>'家庭','3'=>'健康','4'=>'学习','5'=>'人际关系'])?>

        <div class="form-group">
            <label class="control-label">事业</label>
        </div>

        <div class="form-group">
            <label class="control-label">标题2</label>
            <input type="text" name="title[1][2][]" />
        </div>

        <div class="form-group">
            <label class="control-label">数量</label>
            <input type="text" name="target[1][2][]" />
        </div>

        <div class="form-group">
            <label class="control-label">标题1</label>
            <input type="text" name="title[1][1][]" />
        </div>

        <div class="form-group">
            <label class="control-label">数量</label>
            <input type="text" name="target[1][1][]" />
        </div>

        <div class="form-group">
            <label class="control-label">标题3</label>
            <input type="text" name="title[1][3][]" />
        </div>

        <div class="form-group">
            <label class="control-label">数量</label>
            <input type="text" name="target[1][3][]" />
        </div>

        <div class="form-group">
            <label class="control-label">感召</label>
        </div>

        <div class="form-group">
            <label class="control-label">感召标题1</label>
            <input type="text" name="title[2][0][]" />
        </div>

        <div class="form-group">
            <label class="control-label">数量</label>
            <input type="text" name="target[2][0][]" />
        </div>

        <div class="form-group">
            <label class="control-label">感召</label>
        </div>

        <div class="form-group">
            <label class="control-label">社服标题1</label>
            <input type="text" name="title[3][0][]" />
        </div>

        <div class="form-group">
            <label class="control-label">数量</label>
            <input type="text" name="target[3][0][]" />
        </div>
    
        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- plan-create -->
