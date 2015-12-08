<?php
/**
 * Created by PhpStorm.
 * User: Bookin
 * Date: 08.12.2015
 * Time: 23:32
 */

namespace bookin\walletone;

use yii\base\Exception;
use yii\base\Widget;

class WalletOneButton extends Widget
{
    use TWalletOne;

    /**
     * @var array
     */
    public $buttonOptions = ['class'=>'btn btn-info'];

    public $component = 'walletone';

    public function init(){
        parent::init();

        try{
            /** @var self $component */
            $component = \Yii::$app->{$this->component};
            if($component){
                if(!$this->secretKey && $component->secretKey){
                    $this->secretKey = $component->secretKey;
                }
                if($component->walletOptions){
                    $this->walletOptions = self::array_merge_recursive_distinct($component->walletOptions, $this->walletOptions);
                }
                if($component->buttonOptions){
                    $this->buttonOptions = self::array_merge_recursive_distinct($component->buttonOptions, $this->buttonOptions);
                }

                if(!$this->signatureMethod && $component->signatureMethod){
                    $this->signatureMethod = $component->signatureMethod;
                }
            }
        }catch (Exception $c){}

        if(!$this->signatureMethod){
            $this->signatureMethod = WalletOne::SIGNATURE_SHA1;
        }
    }

    public function run()
    {
        \Yii::$app->request->enableCsrfValidation = false;

        $formData = $this->getFields();

        return $this->render('form',[
            'formData' => $formData
        ]);
    }

}