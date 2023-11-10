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
        $apecApi->createOrder($params);
        exit(1);
    }

    /**
     * @param $orderID
     * @return void
     */
    public function actionStatusOrder($orderID)
    {
//        $orderID=10;
        $apecApi= new ApecApiComponent();
        $apecApi->statusOrder($orderID);
        exit(1);
    }
}