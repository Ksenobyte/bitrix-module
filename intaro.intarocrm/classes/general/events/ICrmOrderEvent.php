<?php
/**
 * OrderEvent
 */
class ICrmOrderEvent {
    
    protected static $MODULE_ID = 'intaro.intarocrm';
    protected static $CRM_API_HOST_OPTION = 'api_host';
    protected static $CRM_API_KEY_OPTION = 'api_key';
    protected static $CRM_ORDER_TYPES_ARR = 'order_types_arr';
    protected static $CRM_DELIVERY_TYPES_ARR = 'deliv_types_arr';
    protected static $CRM_PAYMENT_TYPES = 'pay_types_arr';
    protected static $CRM_PAYMENT_STATUSES = 'pay_statuses_arr';
    protected static $CRM_PAYMENT = 'payment_arr'; //order payment Y/N
    protected static $CRM_ORDER_LAST_ID = 'order_last_id';
    protected static $CRM_ORDER_PROPS = 'order_props';
    protected static $CRM_LEGAL_DETAILS = 'legal_details';
    protected static $CRM_CUSTOM_FIELDS = 'custom_fields';
    protected static $CRM_CONTRAGENT_TYPE = 'contragent_type';
    protected static $CRM_ORDER_FAILED_IDS = 'order_failed_ids';
    protected static $CRM_SITES_LIST = 'sites_list';
    protected static $CRM_APPROVED_ORDERS = 'approved_orders';
    protected static $CRM_DISCOUNT_TEXT = 'discount_text';
    protected static $CRM_DISCOUNT_PHOTO = 'discount_photo';
    protected static $CRM_DISCOUNT_VIDEO = 'discount_video';

    
    /**
     * onBeforeOrderAdd
     * 
     * @param mixed $arFields - Order arFields
     */
    function onBeforeOrderAdd($arFields = array()) {
        $GLOBALS['INTARO_CRM_ORDER_ADD'] = true;
        return;
    }

    /**
     * OnSaleBeforeReserveOrder
     *
     * @param mixed $arFields - Order arFields
     */
    function OnSaleBeforeReserveOrder($arFields = array()) {
        $GLOBALS['INTARO_CRM_ORDER_RESERVE'] = true;
        return;
    }

    /**
     * OnSaleReserveOrder
     *
     * @param mixed $arFields - Order arFields
     */
    function OnSaleReserveOrder($arFields = array()) {
        if(isset($GLOBALS['INTARO_CRM_ORDER_RESERVE']) && $GLOBALS['INTARO_CRM_ORDER_RESERVE'])
            unset($GLOBALS['INTARO_CRM_ORDER_RESERVE']);
        return;
    }
    
    /**
     * onUpdateOrder
     * 
     * @param mixed $ID - Order id  
     * @param mixed $arFields - Order arFields
     */
    function onUpdateOrder($ID, $arFields) {
        if(isset($GLOBALS['INTARO_CRM_ORDER_ADD']) && $GLOBALS['INTARO_CRM_ORDER_ADD'])
            return;

        if(isset($GLOBALS['INTARO_CRM_ORDER_RESERVE']) && $GLOBALS['INTARO_CRM_ORDER_RESERVE'])
            return;

        if(isset($GLOBALS['INTARO_CRM_FROM_HISTORY']) && $GLOBALS['INTARO_CRM_FROM_HISTORY'])
            return;
        
        if(isset($arFields['LOCKED_BY']) && $arFields['LOCKED_BY'])
            return;

        if(isset($arFields['CANCELED']))
            return;
        
        self::writeDataOnOrderCreate($ID, $arFields);
    }
    

    /**
     * onSendOrderMail
     * in: sale.order.ajax, sale.order.full
     * 
     * @param mixed $ID - Order id
     * @param mixed $eventName - Event type
     * @param mixed $arFields - Order arFields for sending template
     */
    function onSendOrderMail($ID, &$eventName, &$arFields) {
        if(isset($GLOBALS['INTARO_CRM_FROM_HISTORY']) && $GLOBALS['INTARO_CRM_FROM_HISTORY'])
            return;

        if(self::writeDataOnOrderCreate($ID)) 
            COption::SetOptionString(self::$MODULE_ID, self::$CRM_ORDER_LAST_ID, $ID);
        else {
            $failedIds = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_FAILED_IDS, 0));
            if(!$failedIds)
                $failedIds = array();
            
            $failedIds[] = $ID;
            
            COption::SetOptionString(self::$MODULE_ID, self::$CRM_ORDER_FAILED_IDS, serialize($failedIds));
        }
    }

    /**
     * writeDataOnOrderCreate via api
     * 
     * @param $ID - Order Id
     * @param array $arFields
     * @return boolean
     */
    function writeDataOnOrderCreate($ID, $arFields) {
        
        if (!CModule::IncludeModule('iblock')) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::writeDataOnOrderCreate', 'iblock', 'module not found');
            return true;
        }

        if (!CModule::IncludeModule("sale")) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::writeDataOnOrderCreate', 'sale', 'module not found');
            return true;
        }

        if (!CModule::IncludeModule("catalog")) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::writeDataOnOrderCreate', 'catalog', 'module not found');
            return true;
        }

        $GLOBALS['INTARO_CRM_FROM_HISTORY'] = false;

        $api_host = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_HOST_OPTION, 0);
        $api_key = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_KEY_OPTION, 0);

        //saved cat params
        $optionsOrderTypes = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_TYPES_ARR, 0));
        $optionsDelivTypes = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_DELIVERY_TYPES_ARR, 0));
        $optionsPayTypes = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT_TYPES, 0));
        $optionsPayStatuses = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT_STATUSES, 0)); // --statuses
        $optionsPayment = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT, 0));
        $optionsSitesList = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_SITES_LIST, 0));
        $optionsOrderProps = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_ORDER_PROPS, 0));
        $optionsLegalDetails = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_LEGAL_DETAILS, 0));
        $optionsContragentType = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_CONTRAGENT_TYPE, 0));
        $optionsCustomFields = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_CUSTOM_FIELDS, 0));

        $api = new RetailCrm\RestApi($api_host, $api_key);

        $arParams = ICrmOrderActions::clearArr(array(
            'optionsOrderTypes'     => $optionsOrderTypes,
            'optionsDelivTypes'     => $optionsDelivTypes,
            'optionsPayTypes'       => $optionsPayTypes,
            'optionsPayStatuses'    => $optionsPayStatuses,
            'optionsPayment'        => $optionsPayment,
            'optionsOrderProps'     => $optionsOrderProps,
            'optionsLegalDetails'   => $optionsLegalDetails,
            'optionsContragentType' => $optionsContragentType,
            'optionsSitesList'      => $optionsSitesList,
            'optionsCustomFields'   => $optionsCustomFields
        ));

        $arOrder = CSaleOrder::GetById($ID);

        if (is_array($arFields) && !empty($arFields)) {

            $arFieldsNew = array(
                'USER_ID'        => $arOrder['USER_ID'],
                'ID'             => $ID,
                'PERSON_TYPE_ID' => $arOrder['PERSON_TYPE_ID'],
                'CANCELED'       => $arOrder['CANCELED'],
                'STATUS_ID'      => $arOrder['STATUS_ID'],
                'DATE_INSERT'    => $arOrder['DATE_INSERT'],
                'LID'            => $arOrder['LID']
            );

            $arFieldsNew = array_merge($arFieldsNew, $arFields);
            $arOrder = $arFieldsNew;
        }
        if(count($optionsSitesList)>1){
            $result = ICrmOrderActions::orderCreate($arOrder, $api, $arParams, true, $optionsSitesList[$arOrder['LID']]);
        }
        else{
            $result = ICrmOrderActions::orderCreate($arOrder, $api, $arParams, true);
        }       
         
        if(!$result) {
            ICrmOrderActions::eventLog('ICrmOrderEvent::writeDataOnOrderCreate', 'ICrmOrderActions::orderCreate', 'error during creating order');
            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * @param type $ID -- orderId
     * @param type $cancel -- Y / N - cancel order status
     * @param type $reason -- cancel reason
     * @return boolean
     */
    function onSaleCancelOrder($ID, $cancel, $reason) {
        if(isset($GLOBALS['INTARO_CRM_FROM_HISTORY']) && $GLOBALS['INTARO_CRM_FROM_HISTORY'])
            return;

        if(!$ID || !$cancel)
            return true;
        
        if (!CModule::IncludeModule('iblock')) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::onSaleCancelOrder', 'iblock', 'module not found');
            return true;
        }

        if (!CModule::IncludeModule("sale")) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::onSaleCancelOrder', 'sale', 'module not found');
            return true;
        }

        if (!CModule::IncludeModule("catalog")) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::onSaleCancelOrder', 'catalog', 'module not found');
            return true;
        }

        $api_host = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_HOST_OPTION, 0);
        $api_key = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_KEY_OPTION, 0);

        //saved cat params
        $optionsPayStatuses = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT_STATUSES, 0)); // --statuses

        $api = new RetailCrm\RestApi($api_host, $api_key);

        $order = array();

        if($cancel == 'Y') {
            $order = array(
                'externalId'    => (int) $ID,
                'status'        => $optionsPayStatuses[$cancel.$cancel],
                'statusComment' => ICrmOrderActions::toJSON($reason)
            );
        } else if($cancel == 'N') {
            $arOrder = CSaleOrder::GetById((int) $ID);

            $order = array(
                'externalId'     => (int) $ID,
                'status'         => $optionsPayStatuses[$arOrder['STATUS_ID']],
                'managerComment' => $arOrder['COMMENTS']
            );
        }
        
        $log = new Logger();
        $log->write($order, 'order');
            
        try {
            $api->orderEdit($order);
        } catch (\RetailCrm\Exception\CurlException $e) {
            ICrmOrderActions::eventLog(
                'ICrmOrderEvent::onSaleCancelOrder', 'RetailCrm\RestApi::orderEdit::CurlException',
                $e->getCode() . ': ' . $e->getMessage()
            );
        }
        
        return true;
    }
    
    /**
     * 
     * @param type $ID -- orderId
     * @param type $payed -- Y / N - pay order status
     * @return boolean
     */
    function onSalePayOrder($ID, $payed) {
        if(isset($GLOBALS['INTARO_CRM_FROM_HISTORY']) && $GLOBALS['INTARO_CRM_FROM_HISTORY'])
            return;

        if(!$ID || !$payed || ($payed != 'Y'))
            return true;
        
        if (!CModule::IncludeModule('iblock')) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::onSalePayOrder', 'iblock', 'module not found');
            return true;
        }

        if (!CModule::IncludeModule("sale")) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::onSalePayOrder', 'sale', 'module not found');
            return true;
        }

        if (!CModule::IncludeModule("catalog")) {
            //handle err
            ICrmOrderActions::eventLog('ICrmOrderEvent::onSalePayOrder', 'catalog', 'module not found');
            return true;
        }

        $api_host = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_HOST_OPTION, 0);
        $api_key = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_KEY_OPTION, 0);

        //saved cat params
        $optionsPayment = unserialize(COption::GetOptionString(self::$MODULE_ID, self::$CRM_PAYMENT, 0));

        $api = new RetailCrm\RestApi($api_host, $api_key);
        
        $order = array(
            'externalId'    => (int) $ID,
            'paymentStatus' => $optionsPayment[$payed]
        );
        
        $log = new Logger();
        $log->write($order, 'order');

        try {
            $api->orderEdit($order);
        } catch (\RetailCrm\Exception\CurlException $e) {
            ICrmOrderActions::eventLog(
                'ICrmOrderEvent::onSalePayOrder', 'RetailCrm\RestApi::orderEdit::CurlException',
                $e->getCode() . ': ' . $e->getMessage()
            );
        }
        
        return true;
    }

    /**
     * 
     * @param type $ID -- orderId
     * @param type $value -- ACCOUNT_NUMBER
     * @return boolean
     */
    function onBeforeOrderAccountNumberSet($ID, $value) {
        if(isset($GLOBALS['ICRM_ACCOUNT_NUMBER']) && $GLOBALS['ICRM_ACCOUNT_NUMBER'])
            return $GLOBALS['ICRM_ACCOUNT_NUMBER'];

        return false;
    }

    /**
    * 
    * @param type $discountType -- type of discount
    * @return integer
    */
    protected function getReviewDiscount($discountType) {
        if ( empty($discountType) || !( $discountType == self::$CRM_DISCOUNT_TEXT || $discountType == self::$CRM_DISCOUNT_PHOTO || $discountType == self::$CRM_DISCOUNT_VIDEO) ) {
            throw new \RetailCrm\Exception\WrongParamException('discountType');
        } else {
            return COption::GetOptionString(self::$MODULE_ID, $discountType, 0);
        }
        return 0;
    }

    protected function getProductOffers($product_id) {
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('catalog');

        $result = array($product_id);
        $iblock_info = CCatalogSKU::GetInfoByProductIBlock(2);

        $dbOffers = CIBlockElement::GetList(
            array('ID' => 'DESC'),
            array('PROPERTY_' . $iblock_info['SKU_PROPERTY_ID'] => $product_id),
            false,
            false,
            array('ID')
        );

        while ($dbOffer = $dbOffers->Fetch()) {
            $result[] = $dbOffer['ID'];
        }

        return $result;
    }

    /**
    * 
    * 
    * 
    * 
    * @return boolean
    */
    public function onBeforeCommentUpdate($userId, $productId, $data, $orderId=null) {
        if( empty($userId) || $userId<1 ) {
            throw new \RetailCrm\Exception\WrongParamException('userId');
            return true;
        } elseif( $orderId<1 && $orderId != null ) {
            throw new \RetailCrm\Exception\WrongParamException('orderId');
            return true;
        } elseif( empty($productId) || $productId<1 ) {
            throw new \RetailCrm\Exception\WrongParamException('productId');
            return true;
        } elseif( empty($data) || !is_array($data) ) {
            throw new \RetailCrm\Exception\WrongParamException('data');
            return true;
        } elseif( !(
                    isset($data['reviewText']) && 
                    isset($data['reviewMark']) && 
                    isset($data['reviewType'])
                   ) ) {
            throw new \RetailCrm\Exception\WrongParamException('data', 'array_keys');
            return true;
        } else {
            if ($orderId == null) {
                $arOrder = array('ID' => 'DESC');
                $dbOrders = CSaleOrder::GetList(
                    $arOrder,
                    array('USER_ID' => $userId),
                    false,
                    false,
                    array('ID')
                );

                while ($order = $dbOrders->Fetch()) {
                    $order_ids[] = $order['ID'];
                }

                $basket = CSaleBasket::GetList(
                    $arOrder,
                    array('ORDER_ID' => $order_ids, 'PRODUCT_ID' => self::getProductOffers($productId)),
                    false,
                    false,
                    array('PRODUCT_ID', 'ORDER_ID')
                )->Fetch();

                if ( $basket ) {
                    $orderId = $basket['ORDER_ID'];
                } else {
                    throw new \RetailCrm\Exception\WrongParamException('orderId','search');
                    return true;
                }
            }
            $query_result = COption::GetOptionString(self::$MODULE_ID, self::$CRM_APPROVED_ORDERS, "");
            if($query_result != "") {
                $order_array = unserialize($query_result);
            } else {
                $order_array = array();
            }
            $data['reviewDiscount'] = self::getReviewDiscount('discount_' . $data['reviewType']);
            unset($data['reviewType']);
            $order_array[] = array('USER_ID' => $userId, 'ORDER_ID' => $orderId, 'ELEMENT_ID' => $productId, 'DATA' => $data);
            COption::SetOptionString(self::$MODULE_ID, self::$CRM_APPROVED_ORDERS, serialize($order_array));

        }
        return true;
    }

    public function onCommentAdd($userId, $productId, $data, $orderId = null) {
        if( empty($userId) || $userId<1 ) {
            throw new \RetailCrm\Exception\WrongParamException('userId');
            return true;
        } elseif( empty($productId) || $productId<1 ) {
            throw new \RetailCrm\Exception\WrongParamException('productId');
            return true;
        } elseif( $orderId<1 && orderId != null) {
            throw new \RetailCrm\Exception\WrongParamException('orderId');
            return true;
        } elseif( empty($data) || !is_array($data) ) {
            throw new \RetailCrm\Exception\WrongParamException('data');
            return true;
        } elseif( !isset($data['reviewType']) ) {
            throw new \RetailCrm\Exception\WrongParamException('data', 'array_keys');
            return true;
        } else {
            if ($orderId == null) {
                $arOrder = array('ID' => 'DESC');
                $dbOrders = CSaleOrder::GetList(
                    $arOrder,
                    array('USER_ID' => $userId),
                    false,
                    false,
                    array('ID')
                );

                while ($order = $dbOrders->Fetch()) {
                    $order_ids[] = $order['ID'];
                }

                $basket = CSaleBasket::GetList(
                    $arOrder,
                    array('ORDER_ID' => $order_ids, 'PRODUCT_ID' => self::getProductOffers($productId)),
                    false,
                    false,
                    array('PRODUCT_ID', 'ORDER_ID')
                )->Fetch();

                if ( $basket ) {
                    $orderId = $basket['ORDER_ID'];
                } else {
                    throw new \RetailCrm\Exception\WrongParamException('orderId','search');
                    return true;
                }
            }

            $api_host = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_HOST_OPTION, 0);
            $api_key = COption::GetOptionString(self::$MODULE_ID, self::$CRM_API_KEY_OPTION, 0);

            $api = new RetailCrm\RestApi($api_host, $api_key);
            $data['reviewProductId'] = self::getProductOffers($productId);
            $data['reviewDiscount'] = self::getReviewDiscount('discount_' . $data['reviewType']);
            unset($data['reviewType']);
            $data['reviewApproved'] = 1;
            $order = array(
                'externalId'    => $orderId,
                'customFields' => $data
            );

            try {
                $api->orderEdit($order);
            } catch (\RetailCrm\Exception\CurlException $e) {
                throw new \RetailCrm\Exception\DispatchInterruptException();
            }
        }
    return true;
    }
}