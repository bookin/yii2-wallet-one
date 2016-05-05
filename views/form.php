<?php
/**
 * @var yii\web\View $this
 * @var string[] $formData
 */
use yii\helpers\Html;

echo Html::beginForm($this->context->apiUrl);
    foreach($formData as $key => $value){
        echo Html::hiddenInput($key, $value);
    }
    echo Html::submitButton($this->context->label, $this->context->buttonOptions);
echo Html::endForm();