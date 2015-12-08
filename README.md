Yii2 component for [WalletOne](https://www.walletone.com/)
============================

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). 

To install, either run

```
$ php composer.phar require bookin/yii2-wallet-one "@dev"
```

or add

```
"bookin/yii2-wallet-one": "@dev"
```

to the ```require``` section of your `composer.json` file.


## Usage

### Configure component:

```php
'walletone'=>[
    'class'=>'bookin\walletone\WalletOne',
    'secretKey'=>'....',
    'signatureMethod'=>'sha1',
    'walletOptions'=>[
        'WMI_MERCHANT_ID'=>...,
        'WMI_CURRENCY_ID'=>...,
        'WMI_SUCCESS_URL'=>['site/payment-success'],
        'WMI_FAIL_URL'=>['site/payment-fail'],
    ]
]
```

```secretKey``` - it your secret key, you can find it on setting integration page your account

```walletOptions``` - it [parameters](https://www.walletone.com/merchant/documentation/#step2) for generating a payment form, you can set permanent data

```signatureMethod``` - EDS creation method, this parameter must be the same as your selected method on the setting integration page your account (default - sha1)

If you don't use encryption method, you need set ```signatureMethod``` and ```secretKey``` to NULL

### Example show form

You can get fields (add some options), and create form:

```php
$action = Yii::$app->walletone->apiUrl;
$formData = Yii::$app->walletone->getFields([
    'WMI_PAYMENT_AMOUNT'=>'1.00',
    'WMI_CURRENCY_ID'=>WalletOne::CurrencyID('UAH'),
    'WMI_DESCRIPTION'=>'Top up the account - '.Yii::$app->user->identity->username,
    'WMI_PAYMENT_NO'=>Yii::$app->user->id
]);
echo Html::beginForm($action);
foreach($formData as $key => $value){
    echo Html::hiddenInput($key, $value);
}
echo Html::submitButton('Pay', ['class'=>'btn btn-info']);
echo Html::endForm();
```

Or use simple form (button)

```php
echo WalletOneButton::widget([
    'walletOptions'=>[
        'WMI_PAYMENT_AMOUNT'=>'1.00',
        'WMI_CURRENCY_ID'=>WalletOne::CurrencyID('UAH'),
        'WMI_DESCRIPTION'=>'Top up the account - '.Yii::$app->user->identity->username,
        'WMI_PAYMENT_NO'=>Yii::$app->user->id
    ]
]);
```

### Example success action

```php
$post = Yii::$app->request->post();

/** @var WalletOne $walletone */
$walletone = Yii::$app->walletone;

try{
    if($walletone->checkPayment($post)){
        $user_id = explode('-',$post)[1];
        $user = User::find()->where(['_id'=>$user_id])->one();
        $user->balance = (float)$post['WMI_PAYMENT_AMOUNT'];
        $user->save();
    }
}catch (ErrorException $c){
    return 'WMI_RESULT=RETRY&WMI_DESCRIPTION='.$c->getMessage();
}
return 'WMI_RESULT=OK';
```


