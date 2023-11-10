<?php


namespace common\components;


//use common\helpers\CURL;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;


/**
 *
 */
class ApecApiComponent extends Component
{
    const URL_BASE = 'https://api.apec-uae.com';
    const URL_GET_TOKEN = '/token';
    const CREATE_ORDER = [
        CURLOPT_URL => self::URL_BASE.'/api/order',
        CURLOPT_CUSTOMREQUEST => 'POST',
    ];
    const STATUS_ORDER = [
        CURLOPT_CUSTOMREQUEST => 'GET',
    ];
    private $httpHeader=[];

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        $option=[
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_URL => self::URL_BASE.self::URL_GET_TOKEN,
            CURLOPT_POSTFIELDS =>'username='.Yii::$app->params['api.apec-uae.login'].'&password='.Yii::$app->params['api.apec-uae.password'].'&grant_type=password',
        ];
        $response=$this->rezult($this->curl($option));;
        if (isset($response['access_token'])){
            $this->httpHeader=[
                'Authorization: Bearer '.$response['access_token'],
                'Content-Type: application/json',
            ];
        }else{
            throw  new InvalidArgumentException('Token is not set');
        }
    }

    /**
     * @param $postfields
     * @return never|null
     */
    public function createOrder($postfields)
    {
        $option=ArrayHelper::merge(self::CREATE_ORDER,[CURLOPT_POSTFIELDS=>$postfields]);
        return $this->rezult($this->curl($option));
    }

    /**
     * @param $orderID
     * @return never|null
     */
    public function statusOrder($orderID){
        $option=ArrayHelper::merge(self::STATUS_ORDER,[CURLOPT_URL => self::URL_BASE.'/api/status'.'/'.$orderID]);
        return $this->rezult($this->curl($option));
    }

    /**
     * @param $response
     * @return never|void|null
     */
    protected function rezult($response){
        if($response==false){
            echo 'Ошибка соединения сервером'.PHP_EOL;
            exit(1);
        }

        $response=Json::decode($response);
        if (isset($response['error'])){
            echo $response['error'].PHP_EOL;
            exit(1);
        }

        return $response;
    }

    /**
     * @param $option
     * @param $postfields
     * @return bool|string
     */
    protected function curl($option){
        $optionDefault=array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $this->httpHeader
        );
        $optionDefault=ArrayHelper::merge($optionDefault,$option);

        $curl = curl_init();
        curl_setopt_array($curl,$optionDefault);
        $response = curl_exec($curl);
        curl_close($curl);

       return $response;
    }
}