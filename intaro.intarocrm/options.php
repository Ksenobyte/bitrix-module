<?php 
IncludeModuleLangFile(__FILE__);
$mid = 'intaro.intarocrm';
$uri = $APPLICATION->GetCurPage() . '?mid=' . htmlspecialchars($mid) . '&lang=' . LANGUAGE_ID;

$CRM_API_HOST_OPTION = 'api_host';
$CRM_API_KEY_OPTION = 'api_key';
$CRM_ORDER_TYPES_ARR = 'order_types_arr';
$CRM_DELIVERY_TYPES_ARR = 'deliv_types_arr';
$CRM_PAYMENT_TYPES = 'pay_types_arr';
$CRM_PAYMENT_STATUSES = 'pay_statuses_arr';
$CRM_PAYMENT = 'payment_arr'; //order payment Y/N
$CRM_ORDER_LAST_ID = 'order_last_id';
$CRM_ORDER_SITES = 'sites_ids';
$CRM_ORDER_PROPS = 'order_props';

if(!CModule::IncludeModule('intaro.intarocrm') 
        || !CModule::IncludeModule('sale'))
    return;

$_GET['errc'] = htmlspecialchars(trim($_GET['errc']));
$_GET['ok'] = htmlspecialchars(trim($_GET['ok']));

if($_GET['errc']) echo CAdminMessage::ShowMessage(GetMessage($_GET['errc']));
if($_GET['ok'] && $_GET['ok'] == 'Y') echo CAdminMessage::ShowNote(GetMessage('ICRM_OPTIONS_OK'));

$arResult = array();
$arResult['orderProps'][0]['NAME'] = GetMessage('FIO');
$arResult['orderProps'][0]['ID'] = 'fio';
$arResult['orderProps'][1]['NAME'] = GetMessage('ZIP');
$arResult['orderProps'][1]['ID'] = 'index';
$arResult['orderProps'][2]['NAME'] = GetMessage('PHONE');
$arResult['orderProps'][2]['ID'] = 'phone';
$arResult['orderProps'][3]['NAME'] = GetMessage('EMAIL');
$arResult['orderProps'][3]['ID'] = 'email';
// address
$arResult['orderProps'][4]['NAME'] = GetMessage('ADDRESS');
$arResult['orderProps'][4]['ID'] = 'text';
$arResult['orderProps'][5]['NAME'] = GetMessage('COUNTRY');
$arResult['orderProps'][5]['ID'] = 'country';
$arResult['orderProps'][6]['NAME'] = GetMessage('REGION');
$arResult['orderProps'][6]['ID'] = 'region';
$arResult['orderProps'][7]['NAME'] = GetMessage('CITY');
$arResult['orderProps'][7]['ID'] = 'city';
$arResult['orderProps'][8]['NAME'] = GetMessage('STREET');
$arResult['orderProps'][8]['ID'] = 'street';
$arResult['orderProps'][9]['NAME'] = GetMessage('BUILDING');
$arResult['orderProps'][9]['ID'] = 'building';
$arResult['orderProps'][10]['NAME'] = GetMessage('FLAT');
$arResult['orderProps'][10]['ID'] = 'flat';
$arResult['orderProps'][11]['NAME'] = GetMessage('INTERCOMCODE');
$arResult['orderProps'][11]['ID'] = 'intercomecode';
$arResult['orderProps'][12]['NAME'] = GetMessage('FLOOR');
$arResult['orderProps'][12]['ID'] = 'floor';
$arResult['orderProps'][13]['NAME'] = GetMessage('BLOCK');
$arResult['orderProps'][13]['ID'] = 'block';
$arResult['orderProps'][14]['NAME'] = GetMessage('HOUSE');
$arResult['orderProps'][14]['ID'] = 'house';

//update connection settings
if (isset($_POST['Update']) && ($_POST['Update'] == 'Y')) {
    $api_host = htmlspecialchars(trim($_POST['api_host']));
    $api_key = htmlspecialchars(trim($_POST['api_key']));
    
    // if empty so select all? or exception --not obligatory
    $orderSites = array();
    foreach ($_POST[$CRM_ORDER_SITES] as $site) {
        $orderSites[] = htmlspecialchars(trim($site));
    }
            
    if($api_host && $api_key) {
        $api = new IntaroCrm\RestApi($api_host, $api_key);
            
        $api->paymentStatusesList();
            
        //check connection & apiKey valid
        if((int) $api->getStatusCode() != 200) {
            $uri .= '&errc=ERR_' . $api->getStatusCode();
            LocalRedirect($uri);
        } else {
            COption::SetOptionString($mid, 'api_host', $api_host);
            COption::SetOptionString($mid, 'api_key', $api_key);
        }   
    }
	
    //bitrix orderTypesList -- personTypes
    $dbOrderTypesList = CSalePersonType::GetList(
        array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ),
        array(
             "ACTIVE" => "Y",
        ),
        false,
        false,
        array()
    );
            
    //form order types ids arr
    $orderTypesArr = array();
    if ($arOrderTypesList = $dbOrderTypesList->Fetch()) {
        do {
            $orderTypesArr[$arOrderTypesList['ID']] = $_POST['order-type-' . $arOrderTypesList['ID']];     
        } while ($arOrderTypesList = $dbOrderTypesList->Fetch());
    }
	
    //bitrix deliveryTypesList
    $dbDeliveryTypesList = CSaleDelivery::GetList(
        array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ),
        array(
             "ACTIVE" => "Y",
        ),
        false,
        false,
        array()
    );
            
    //form delivery types ids arr
    $deliveryTypesArr = array();
    if ($arDeliveryTypesList = $dbDeliveryTypesList->Fetch()) {
        do {
            $deliveryTypesArr[$arDeliveryTypesList['ID']] = htmlspecialchars(trim($_POST['delivery-type-' . $arDeliveryTypesList['ID']]));   
        } while ($arDeliveryTypesList = $dbDeliveryTypesList->Fetch());
    }
		    
    //bitrix paymentTypesList
    $dbPaymentTypesList = CSalePaySystem::GetList(
        array(
            "SORT" => "ASC", 
            "NAME" => "ASC"
        ), 
        array(
            "ACTIVE" => "Y"
        )
    );
        
    //form payment types ids arr
    $paymentTypesArr = array();
    if ($arPaymentTypesList = $dbPaymentTypesList->Fetch()) {
        do {
            $paymentTypesArr[$arPaymentTypesList['ID']] = htmlspecialchars(trim($_POST['payment-type-' . $arPaymentTypesList['ID']]));         
        } while ($arPaymentTypesList = $dbPaymentTypesList->Fetch());
    }
                
    //bitrix paymentStatusesList
    $dbPaymentStatusesList = CSaleStatus::GetList(
        array(
            "SORT" => "ASC", 
            "NAME" => "ASC"
        ), 
        array(
            "LID" => "ru", //ru 
            "ACTIVE" => "Y"
          )
    );
            
    //form payment statuses ids arr
    $paymentStatusesArr['Y'] = htmlspecialchars(trim($_POST['payment-status-Y']));
    if ($arPaymentStatusesList = $dbPaymentStatusesList->Fetch()) {
        do {
            $paymentStatusesArr[$arPaymentStatusesList['ID']] = htmlspecialchars(trim($_POST['payment-status-' . $arPaymentStatusesList['ID']]));     
        } while ($arPaymentStatusesList = $dbPaymentStatusesList->Fetch());
    }
    
    //form payment ids arr
    $paymentArr = array();
    $paymentArr['Y'] = htmlspecialchars(trim($_POST['payment-Y']));
    $paymentArr['N'] = htmlspecialchars(trim($_POST['payment-N']));
    
    $propsCount = 0;
    $orderPropsArr = array();
    foreach($arResult['orderProps'] as $orderProp) {
        if((!(int) htmlspecialchars(trim($_POST['address-detail']))) && $propsCount > 4) 
            break;
        $orderPropsArr[$orderProp['ID']] = htmlspecialchars(trim($_POST['order-prop-' . $orderProp['ID']]));
        $propsCount++;
    }
    
    COption::SetOptionString($mid, $CRM_ORDER_TYPES_ARR, serialize($orderTypesArr));
    COption::SetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, serialize($deliveryTypesArr));
    COption::SetOptionString($mid, $CRM_PAYMENT_TYPES, serialize($paymentTypesArr));
    COption::SetOptionString($mid, $CRM_PAYMENT_STATUSES, serialize($paymentStatusesArr));
    COption::SetOptionString($mid, $CRM_PAYMENT, serialize($paymentArr));
    COption::SetOptionString($mid, $CRM_ORDER_SITES, serialize($orderSites));
    COption::SetOptionString($mid, $CRM_ORDER_PROPS, serialize($orderPropsArr));

    $uri .= '&ok=Y';
    LocalRedirect($uri);
} else {
    $api_host = COption::GetOptionString($mid, $CRM_API_HOST_OPTION, 0);
    $api_key = COption::GetOptionString($mid, $CRM_API_KEY_OPTION, 0);

    $api = new IntaroCrm\RestApi($api_host, $api_key);
    
    $arResult['arSites'] = array();
    $rsSites = CSite::GetList($by, $sort, array());
    while ($ar = $rsSites->Fetch())
        $arResult['arSites'][] = $ar;

    //prepare crm lists
    $arResult['orderTypesList'] = $api->orderTypesList();
    $arResult['deliveryTypesList'] = $api->deliveryTypesList();
    $arResult['paymentTypesList'] = $api->paymentTypesList();
    $arResult['paymentStatusesList'] = $api->paymentStatusesList(); // --statuses
    $arResult['paymentList'] = $api->orderStatusesList();
    $arResult['paymentGroupList'] = $api->orderStatusGroupsList(); // -- statuses groups
            
    //check connection & apiKey valid
    if ((int) $api->getStatusCode() != 200)
        echo CAdminMessage::ShowMessage(GetMessage('ERR_' . $api->getStatusCode()));

    //bitrix orderTypesList -- personTypes
    $dbOrderTypesList = CSalePersonType::GetList(
        array(
            "SORT" => "ASC",
            "NAME" => "ASC"
         ),
        array(
            "ACTIVE" => "Y",
        ),
        false,
        false,
        array()
     );
            
     if ($arOrderTypesList = $dbOrderTypesList->Fetch()) {
        do {
            $arResult['bitrixOrderTypesList'][] = $arOrderTypesList;     
        } while ($arOrderTypesList = $dbOrderTypesList->Fetch());
    }

    //bitrix deliveryTypesList
    $dbDeliveryTypesList = CSaleDelivery::GetList(
        array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ),
        array(
             "ACTIVE" => "Y",
        ), 
        false, 
        false, 
        array()
    );

    if ($arDeliveryTypesList = $dbDeliveryTypesList->Fetch()) {
        do {
            $arResult['bitrixDeliveryTypesList'][] = $arDeliveryTypesList;
        } while ($arDeliveryTypesList = $dbDeliveryTypesList->Fetch());
    }

    //bitrix paymentTypesList
    $dbPaymentTypesList = CSalePaySystem::GetList(
        array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ),
        array(
             "ACTIVE" => "Y"
        )
    );

    if ($arPaymentTypesList = $dbPaymentTypesList->Fetch()) {
        do {
            $arResult['bitrixPaymentTypesList'][] = $arPaymentTypesList;
        } while ($arPaymentTypesList = $dbPaymentTypesList->Fetch());
    }

    //bitrix paymentStatusesList
    $dbPaymentStatusesList = CSaleStatus::GetList(
        array(
            "SORT" => "ASC",
            "NAME" => "ASC"
        ),
        array(
            "LID" => "ru", //ru 
            "ACTIVE" => "Y"
        )
    );

    if ($arPaymentStatusesList = $dbPaymentStatusesList->Fetch()) {
        do {
            $arResult['bitrixPaymentStatusesList'][] = $arPaymentStatusesList;
        } while ($arPaymentStatusesList = $dbPaymentStatusesList->Fetch());
    }
    $arResult['bitrixPaymentStatusesList'][] = array(
        'ID'   => 'Y',
        'NAME' => GetMessage('CANCELED')
    );
    
    //bitrix pyament Y/N
    $arResult['bitrixPaymentList'][0]['NAME'] = GetMessage('PAYMENT_Y');
    $arResult['bitrixPaymentList'][0]['ID'] = 'Y';
    $arResult['bitrixPaymentList'][1]['NAME'] = GetMessage('PAYMENT_N');
    $arResult['bitrixPaymentList'][1]['ID'] = 'N';
    
    $dbProp = CSaleOrderProps::GetList(array(), array());
    while ($arProp = $dbProp->GetNext()) {
        $arResult['arProp'][] = $arProp;
    }

    //saved cat params
    $optionsOrderTypes = unserialize(COption::GetOptionString($mid, $CRM_ORDER_TYPES_ARR, 0));
    $optionsDelivTypes = unserialize(COption::GetOptionString($mid, $CRM_DELIVERY_TYPES_ARR, 0));
    $optionsPayTypes = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_TYPES, 0));
    $optionsPayStatuses = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT_STATUSES, 0)); // --statuses
    $optionsPayment = unserialize(COption::GetOptionString($mid, $CRM_PAYMENT, 0));
    $optionsSites = unserialize(COption::GetOptionString($mid, $CRM_ORDER_SITES, 0));
    $optionsOrderProps = unserialize(COption::GetOptionString($mid, $CRM_ORDER_PROPS, 0));

    $aTabs = array(
        array(
            "DIV" => "edit1",
            "TAB" => GetMessage('ICRM_OPTIONS_GENERAL_TAB'),
            "ICON" => "",
            "TITLE" => GetMessage('ICRM_OPTIONS_GENERAL_CAPTION')
        ),
        array(
            "DIV" => "edit2",
            "TAB" => GetMessage('ICRM_OPTIONS_CATALOG_TAB'),
            "ICON" => '',
            "TITLE" => GetMessage('ICRM_OPTIONS_CATALOG_CAPTION')
        ),
        array(
            "DIV" => "edit3",
            "TAB" => GetMessage('ICRM_OPTIONS_ORDER_PROPS_TAB'),
            "ICON" => '',
            "TITLE" => GetMessage('ICRM_OPTIONS_ORDER_PROPS_CAPTION')
        ),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
?>
<?php $APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/js/main/jquery/jquery-1.7.min.js"></script>'); ?>
<script type="text/javascript">
    $(document).ready(function() { 
        $('input[name="address-detail"]').change(function(){  
            if(parseInt($(this).val()) === 1)
                $('tr.address-detail').show('slow');
            else if(parseInt($(this).val()) === 0)
                $('tr.address-detail').hide('slow');
                
            });
     });
</script>

<form method="POST" action="<?php echo $uri; ?>" id="FORMACTION">
<?php 
    echo bitrix_sessid_post();
    $tabControl->BeginNextTab();
?>
    <input type="hidden" name="tab" value="catalog">
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ICRM_CONN_SETTINGS'); ?></b></td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?php echo GetMessage('ICRM_API_HOST'); ?></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="text" id="api_host" name="api_host" value="<?php echo $api_host; ?>"></td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?php echo GetMessage('ICRM_API_KEY'); ?></td>
        <td width="50%" class="adm-detail-content-cell-r"><input type="text" id="api_key" name="api_key" value="<?php echo $api_key; ?>"></td>
    </tr>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l"><?php echo GetMessage('ICRM_SITES'); ?></td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select id="sites_ids" name="sites_ids[]" multiple="multiple" size="3">
                <?php foreach ($arResult['arSites'] as $site): ?>
                    <option value="<?php echo $site['LID'] ?>" <?php if(in_array($site['LID'], $optionsSites)) echo 'selected="selected"'; ?>><?php echo $site['NAME'] . ' (' . $site['LID'] . ')' ?></option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
<?php $tabControl->BeginNextTab(); ?>
    <input type="hidden" name="tab" value="catalog">
    <tr align="center">
        <td colspan="2"><b><?php echo GetMessage('INFO_1'); ?></b></td>
    </tr>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('DELIVERY_TYPES_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixDeliveryTypesList'] as $bitrixDeliveryType): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixDeliveryType['ID']; ?>">
    	    <?php echo $bitrixDeliveryType['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="delivery-type-<?php echo $bitrixDeliveryType['ID']; ?>" class="typeselect">
                <option value=""></option>
                <?php foreach($arResult['deliveryTypesList'] as $deliveryType): ?>
                <option value="<?php echo $deliveryType['code']; ?>" <?php if ($optionsDelivTypes[$bitrixDeliveryType['ID']] == $deliveryType['code']) echo 'selected'; ?>>
                    <?php echo $APPLICATION->ConvertCharset($deliveryType['name'], 'utf-8', SITE_CHARSET); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('PAYMENT_TYPES_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixPaymentTypesList'] as $bitrixPaymentType): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixPaymentType['ID']; ?>">
            <?php echo $bitrixPaymentType['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="payment-type-<?php echo $bitrixPaymentType['ID']; ?>" class="typeselect">
                <option value="" selected=""></option>
                <?php foreach($arResult['paymentTypesList'] as $paymentType): ?>
                <option value="<?php echo $paymentType['code']; ?>" <?php if ($optionsPayTypes[$bitrixPaymentType['ID']] == $paymentType['code']) echo 'selected'; ?>>
                    <?php echo $APPLICATION->ConvertCharset($paymentType['name'], 'utf-8', SITE_CHARSET); ?>
                </option>
                <?php endforeach; ?>
             </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('PAYMENT_STATUS_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixPaymentStatusesList'] as $bitrixPaymentStatus): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixPaymentStatus['ID']; ?>">
            <?php echo $bitrixPaymentStatus['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="payment-status-<?php echo $bitrixPaymentStatus['ID']; ?>" class="typeselect">
                <option value=""></option>
                <?php foreach($arResult['paymentGroupList'] as $orderStatusGroup): if(!empty($orderStatusGroup['statuses'])) : ?>
                <optgroup label="<?php echo $orderStatusGroup['name']; ?>">
                    <?php foreach($orderStatusGroup['statuses'] as $payment): ?>
                    <option value="<?php echo $arResult['paymentList'][$payment]['code']; ?>" <?php if ($optionsPayStatuses[$bitrixPaymentStatus['ID']] == $arResult['paymentList'][$payment]['code']) echo 'selected'; ?>>
                        <?php echo $APPLICATION->ConvertCharset($arResult['paymentList'][$payment]['name'], 'utf-8', SITE_CHARSET); ?>
                    </option>
                    <?php endforeach; ?>
                </optgroup>
                <?php endif; endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('PAYMENT_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixPaymentList'] as $bitrixPayment): ?>
    <tr>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixPayment['ID']; ?>">
            <?php echo $bitrixPayment['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="payment-<?php echo $bitrixPayment['ID']; ?>" class="typeselect">
                <option value=""></option>
                <?php foreach($arResult['paymentStatusesList'] as $paymentStatus): ?>
                <option value="<?php echo $paymentStatus['code']; ?>" <?php if ($optionsPayment[$bitrixPayment['ID']] == $paymentStatus['code']) echo 'selected'; ?>>
                    <?php echo $APPLICATION->ConvertCharset($paymentStatus['name'], 'utf-8', SITE_CHARSET); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDER_TYPES_LIST'); ?></b></td>
    </tr>
    <?php foreach($arResult['bitrixOrderTypesList'] as $bitrixOrderType): ?>
    <tr>
       <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $bitrixOrderType['ID']; ?>">
           <?php echo $bitrixOrderType['NAME']; ?>
	   </td>
	   <td width="50%" class="adm-detail-content-cell-r">
	       <select name="order-type-<?php echo $bitrixOrderType['ID']; ?>" class="typeselect">
               <option value=""></option>
               <?php foreach($arResult['orderTypesList'] as $orderType): ?>
               <option value="<?php echo $orderType['code']; ?>" <?php if ($optionsOrderTypes[$bitrixOrderType['ID']] == $orderType['code']) echo 'selected'; ?>>
                   <?php echo $APPLICATION->ConvertCharset($orderType['name'], 'utf-8', SITE_CHARSET); ?>
               </option>
               <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php endforeach; ?>
<?php $tabControl->BeginNextTab(); ?>
    <input type="hidden" name="tab" value="catalog">
    <tr class="heading">
        <td colspan="2"><b><?php echo GetMessage('ORDER_PROPS'); ?></b></td>
    </tr>
    <?php $countProps = 0; foreach($arResult['orderProps'] as $orderProp): ?>
    <?php if($orderProp['ID'] == 'text'): ?>
    <tr class="heading">
        <td colspan="2">
            <b>
                <label><input type="radio" name="address-detail" value="0" <?php if(count($optionsOrderProps) < 6) echo "checked"; ?>><?php echo GetMessage('ADDRESS_SHORT'); ?></label>
                <label><input type="radio" name="address-detail" value="1" <?php if(count($optionsOrderProps) > 5) echo "checked"; ?>><?php echo GetMessage('ADDRESS_FULL'); ?></label>
            </b>
        </td>
    </tr>
    <?php endif; ?>
    <tr <?php if ($countProps > 4) echo 'class="address-detail"'; if(($countProps > 4) && (count($optionsOrderProps) < 6)) echo 'style="display:none;"';?>>
        <td width="50%" class="adm-detail-content-cell-l" name="<?php echo $orderProp['ID']; ?>">
    	    <?php echo $orderProp['NAME']; ?>
        </td>
        <td width="50%" class="adm-detail-content-cell-r">
            <select name="order-prop-<?php echo $orderProp['ID']; ?>" class="typeselect">
                <option value=""></option>              
                <?php foreach ($arResult['arProp'] as $arProp): ?>
                <option value="<?php echo $arProp['CODE']; ?>" <?php if ($optionsOrderProps[$orderProp['ID']] == $arProp['CODE']) echo 'selected'; ?>>
                    <?php echo $arProp['NAME']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <?php $countProps++; endforeach; ?>
<?php $tabControl->BeginNextTab(); ?>
<?php $tabControl->Buttons(); ?>
<input type="hidden" name="Update" value="Y" />
<input type="submit" title="<?php echo GetMessage('ICRM_OPTIONS_SUBMIT_TITLE'); ?>" value="<?php echo GetMessage('ICRM_OPTIONS_SUBMIT_VALUE'); ?>" name="btn-update" class="adm-btn-save" />
<?php $tabControl->End(); ?>
</form>

<?php } ?>