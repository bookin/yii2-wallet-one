<?php

namespace bookin\walletone;

use yii\base\Component;
use yii\base\ErrorException;
use yii\helpers\Url;

class WalletOne extends Component
{
    use TWalletOne;

    const SIGNATURE_SHA1 = 'sha1';
    const SIGNATURE_MD5 = 'md5';

    const ERROR_SIGNATURE = 101;
    const ERROR_UNKNOWN = 201;
    const ERROR_HAVE_NOT_CURRENCY = 301;

    protected static $CURRENCY_ID = [
        'RUB'=>643,
        'ZAR'=>710,
        'USD'=>840,
        'EUR'=>978,
        'UAH'=>980,
        'KZT'=>398,
        'BYR'=>974,
        'TJS'=>972
    ];

    public function init(){
        parent::init();

        if(!$this->signatureMethod){
            $this->signatureMethod = self::SIGNATURE_SHA1;
        }
    }

    /**
     * @param $post
     * @return bool
     * @throws ErrorException
     * @throws WalletOneException
     */
    public function checkPayment($post){
        $success = $this->checkSignature($this->secretKey, $post, $this->signatureMethod);
        if ($success){
            if (strtoupper($post["WMI_ORDER_STATE"]) == "ACCEPTED")
            {
                return true;
            }else{
                throw new WalletOneException($_POST["WMI_ORDER_STATE"], self::ERROR_UNKNOWN);
            }
        }else{
            throw new WalletOneException('Incorrect signature', self::ERROR_UNKNOWN);
        }
    }

    /**
     * @param $code - ISO 4217 currency code
     * @return mixed
     * @throws WalletOneException
     */
    public static function CurrencyID($code){
        if(isset(self::$CURRENCY_ID[$code])){
            return self::$CURRENCY_ID[$code];
        }else{
            throw new WalletOneException('WalletOne do not support '.$code, self::ERROR_HAVE_NOT_CURRENCY);
        }
    }
}