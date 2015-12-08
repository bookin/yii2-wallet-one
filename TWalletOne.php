<?php
/**
 * Created by PhpStorm.
 * User: Bookin
 * Date: 08.12.2015
 * Time: 23:36
 */

namespace bookin\walletone;


use yii\base\ErrorException;
use yii\helpers\Url;

trait TWalletOne
{
    public $apiUrl = 'https://wl.walletone.com/checkout/checkout/Index';
    public $secretKey = '';
    public $walletOptions = [];

    /**
     * @var string Default sha1
     */
    public $signatureMethod;

    /**
     * @throws ErrorException
     */
    protected function checkOptions($options=[]){
        $walletOptions = $options ?: $this->walletOptions;

        $required = [
            'WMI_MERCHANT_ID',
            'WMI_PAYMENT_AMOUNT',
            'WMI_CURRENCY_ID',
            'WMI_DESCRIPTION',
            'WMI_SUCCESS_URL',
            'WMI_FAIL_URL'
        ];
        if($walletOptions){
            foreach($required as $key=>$val){
                if(array_key_exists($val,$walletOptions)){
                    unset($required[$key]);
                }
            }
        }
        if($required){
            throw new ErrorException('Error configuration WalletOne, need set - '.implode(', ',$required));
        }
    }

    /**
     * @param array $options this parameter will be merge with walletOptions
     * @return $this
     */
    public function addWalletOptions($options=[]){
        $this->walletOptions = self::array_merge_recursive_distinct($options, $this->walletOptions);
        return $this;
    }


    /**
     * @param array $options
     * @return array
     * @throws ErrorException
     */
    public function getFields($options=[]){
        $fields = self::array_merge_recursive_distinct($options, $this->walletOptions);

        $this->checkOptions($fields);

        if(isset($fields['WMI_DESCRIPTION'])){
            $fields['WMI_DESCRIPTION'] = 'BASE64:' . base64_encode($fields['WMI_DESCRIPTION']);
        }
        if(isset($fields['WMI_SUCCESS_URL']) && is_array($fields['WMI_SUCCESS_URL'])){
            $fields['WMI_SUCCESS_URL'] = Url::toRoute($fields['WMI_SUCCESS_URL'], true);
        }
        if(isset($fields['WMI_FAIL_URL']) && is_array($fields['WMI_FAIL_URL'])){
            $fields['WMI_FAIL_URL'] = Url::toRoute($fields['WMI_FAIL_URL'], true);
        }

        $formData['WMI_PAYMENT_AMOUNT'] = number_format($fields['WMI_PAYMENT_AMOUNT'], 2, '.', ',');

        if(isset($this->secretKey) && isset($this->signatureMethod)){
            $signature = self::getSignature($this->secretKey, $fields, $this->signatureMethod);
            $fields["WMI_SIGNATURE"] = $signature;
        }

        return $fields;
    }

    /**
     * @param $secretKey
     * @param array $data
     * @param string $signatureMethod
     * @return string
     */
    protected static function getSignature($secretKey, $data, $signatureMethod = 'sha1'){
        unset($data['WMI_SIGNATURE']);

        foreach ($data as $name => $val) {
            if (is_array($val)) {
                usort($val, "strcasecmp");
                $data[$name] = $val;
            }
        }
        uksort($data, "strcasecmp");
        $fieldValues = "";
        foreach ($data as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $v = iconv("utf-8", "windows-1251", $v);
                    $fieldValues .= $v;
                }
            } else {
                $value = iconv("utf-8", "windows-1251", $value);
                $fieldValues .= $value;
            }
        }
        $signature = '';

        if ($signatureMethod == 'md5') {
            $signature = base64_encode(pack("H*", sha1($fieldValues . $secretKey)));
        }
        if ($signatureMethod == 'sha1') {
            $signature = base64_encode(pack("H*", sha1($fieldValues . $secretKey)));
        }

        return $signature;
    }

    /**
     * @param $secretKey
     * @param $data
     * @param string $signatureMethod
     * @return bool
     * @throws ErrorException
     */
    public static function checkSignature($secretKey, $data, $signatureMethod = 'sha1'){
        if($secretKey && !$signatureMethod){
            throw new WalletOneException('Need set signature method');
        }

        if(is_null($secretKey) || is_null($signatureMethod)){
            return true;
        }else if(in_array($signatureMethod,['sha1','md5'])){
            if (!is_array($data) or !isset($data) or is_null($data)) {
                return false;
            }
            $signature = self::getSignature($secretKey, $data, $signatureMethod);
            if($signature == $data["WMI_SIGNATURE"]){
                return true;
            }
        }
        return false;
    }

    public static function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = self::array_merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

}