<?php
declare(strict_types=1);

namespace console\controllers;

use Yii;
use yii\console\{Controller};
use common\components\ApecApiComponent;

class ApecApiController extends Controller
{
    /**
     * @return string
     */
    //php yii apec-api/create-order
    public function actionCreateOrder()
    {
        $params='{
            "CustOrderNum": "!!!ТЕСТОВЫЙ API!!!",
            "OrderNotes": "!!!ТЕСТОВЫЙ API!!!",
            "ValidationType": 1,
            "IsTest": true,
            "OrderHeadLines": [
                {
                    "Count": 1,
                    "Price": 3000,
                    "Reference": "!!!ТЕСТОВЫЙ API DP: 1!!!",
                    "ReactionByCount": 0,
                    "ReactionByPrice": 0,
                    "StrictlyThisNumber": true,
                    "Brand": "MAZDA",
                    "PartNumber": "G51861764A",
                    "SupplierID": 1203
                }
            ],
            "DeliveryPointID": 0
        }';
        $apecApi= new ApecApiComponent();
        $response=$apecApi->createOrder($params);
        print_r($response.PHP_EOL);
        exit(1);
    }

    /**
     * @param $orderID
     * @return void
     */
    //php yii apec-api/status-order
    public function actionStatusOrder($orderID)
    {
//        $orderID=10;
        $apecApi= new ApecApiComponent();
        $response=$apecApi->statusOrder($orderID);
        print_r($response.PHP_EOL);
        exit(1);
    }
}