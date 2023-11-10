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
    const URL_CREATE_ORDER = '/api/order';
    const URL_VIEW_ORDER = '/api/status';



    private $token=null;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => self::URL_BASE.self::URL_GET_TOKEN,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'username='.Yii::$app->params['api.apec-uae.login'].'&password='.Yii::$app->params['api.apec-uae.password'].'&grant_type=password',
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            ));
            $response = Json::decode(curl_exec($curl));
            curl_close($curl);

            if (isset($response['error'])){
                echo $response['error'].PHP_EOL;
                exit(1);
            }

            if (isset($response['access_token'])){
                $this->token=$response['access_token'];
            }else{
                throw  new InvalidArgumentException('Token is not set');
            }
    }

    /**
     * @param $params
     * @return never|null
     */
    public function createOrder($params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::URL_BASE.self::URL_CREATE_ORDER,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$params,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $this->rezult($response);
    }

    /**
     * @param $orderID
     * @return never|null
     */
    public function statusOrder($orderID){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::URL_BASE.self::URL_VIEW_ORDER.'/'.$orderID,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Authorization: Bearer '.$this->token),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $this->rezult($response);
    }

    /**
     * @param $response
     * @return never|void|null
     */
    protected function rezult($response){
        if (isset($response['error'])){
            echo $response['error'].PHP_EOL;
            exit(1);
        }
        return  print_r( Json::decode($response));
//        return  dd( Json::decode($response));
    }
}