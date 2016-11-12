<?php
/**
 * Created by PhpStorm.
 * User: avs
 * Date: 11/1/16
 * Time: 11:06 PM
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin([ 'action' => ['/site/index'], 'options' => ['data-pjax' => '', 'class' => 'form-inline'] ]); ?>
    <?= $form->field($form_model, 'nodes_cnt')->label('Количество узлов дерева')->textInput(['value' => 20]); ?>
    <?= Html::submitButton('Генерировать дерево', ['class' => 'btn btn-primary ', 'id' => 'gen-btn', 'name' => 'gen-button']) ?>
<?php ActiveForm::end();?>