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
    const GET_STATUS_ORDER=[
        '0'=> ' полностью размещён',
        '1' => ' размещён частично',
        '2' => ' не размещён',
    ];
    const FIND_STATUS_ORDER=[
        '1'=> ' новый',
        '2' => ' в работе',
        '3' => ' завершён',
    ];
    const ORDER_PLACED_LINES=[
        '0'=>'Строка успешна размещена',
        '1'=>'Строка успешно размещена. Количество выровнено по кратности вверх.',
        '2'=>'Строка успешно размещена. Количество выровнено по кратности вниз (со снижением до остатка при необходимости).',
        '10'=>'Строка не размещена. Не указано одно из ключевых значений: Brand, PartNumber, SupplierID',
        '11'=>'Строка не размещена. Не указано количество Count.',
        '12'=>'Строка не размещена. Не указана реакция на коллизию по количеству ReactionByCount.',
        '13'=>'Строка не размещена. Не указана реакция на коллизию по цене ReactionByPrice.',
        '20'=>'Строка не размещена. Коллизия по количеству.',
        '30'=>'Строка не размещена. Коллизия по цене.',
        '99'=>'Строка не размещена. Позиция не найдена в остатках.',
    ];
    const ORDER_LINES=[
        '240'=>'new order (Новый заказ)',
        '10'=>'new order (Новый заказ)',
        '40'=>'the order is accepted (Заказ принят)',
        '50'=>'sent to the supplier (Отправлен поставщику)',
        '70'=>'quantity change (Изменение количества)',
        '80'=>'delay (Задержка)',
        '90'=>'superseded part (Переход номера)',
        '100'=>'price change (Изменение цены)',
        '120'=>'in stock (Поступил на склад)',
        '140'=>'ready for dispatch (Готово к выдаче)',
        '160'=>'received by the customer (Получен клиентом)',
        '180'=>'delivery is impossible (Поставка невозможна)',
        '190'=>'dispatch is impossible temporarily (Выдача невозможна)',
        '200'=>'canceled by the customer (Отказ клиента)',
    ];

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
    public function createOrder($postfields,$getStatusOrder=false)
    {
        $option=ArrayHelper::merge(self::CREATE_ORDER,[CURLOPT_POSTFIELDS=>Json::encode($postfields)]);
        $response=$this->rezult($this->curl($option));
        if ($getStatusOrder) $this->getStatusOrder($response);
        return $response;
    }


    /**
     * @param $orderID
     * @return never|null
     */
    public function statusOrder($orderID,$findStatusOrder=false){
        $option=ArrayHelper::merge(self::STATUS_ORDER,[CURLOPT_URL => self::URL_BASE.'/api/status'.'/'.$orderID]);
        $response=$this->rezult($this->curl($option));
        if ($findStatusOrder) $this->findStatusOrder($response);
        return $response;
    }

    /**
     * @param $response
     * @return never|void|null
     */
    protected function rezult($response){
        if($response==false){
            echo 'Ошибка соединения с сервером'.PHP_EOL;
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
    /**
     * @param $response
     * @return void
     */
    public function getStatusOrder($response){
        echo 'Заказ №'.$response['OrderId'].self::GET_STATUS_ORDER[$response['Status']].PHP_EOL;
        foreach ($response['OrderPlacedLines'] as $item)
            echo 'Позиция '.$item['Brand'].' '.$item['PartNumber'].' '.self::ORDER_PLACED_LINES[$item['Status']].PHP_EOL;
    }

    /**
     * @param $response
     * @return void
     */
    public function findStatusOrder($response){
        echo 'Заказ №'.$response['OrderID'].self::FIND_STATUS_ORDER[$response['Status']].PHP_EOL;
        foreach ($response['OrderLines'] as $item)
            echo 'Позиция '.$item['Brand'].' '.$item['PartNumber'].' '.self::ORDER_LINES[$item['CurrentStatus']].PHP_EOL;
    }
}