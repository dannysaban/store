<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *  ICEPAY Basic module for VirtueMart
 *  Main payment class
 *
 *  @version 1.0.0
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2012 ICEPAY B.V.
 *  www.icepay.com
 *
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
if (!class_exists('vmPSPlugin'))
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

require_once(dirname(__FILE__).'/icepay_api/icepay_api_basic.php');



class plgVMPaymentIcepaybasic extends vmPSPlugin {

    // instance of class
    public static $_this = false;
    private $_icepay;
    private $_version = "1.0.1";

    function __construct(& $subject, $config) {
	//if (self::$_this)
	 //   return self::$_this;
	parent::__construct($subject, $config);

        $this->_icepay = new Icepay_Project_Helper();

	$this->_loggable = true;
	$this->tableFields = array_keys($this->getTableSQLFields());

        /* stored to database based on settings */
	$varsToPush = array(
            'icepaybasic_merchantid'    => array('', 'char'),
	    'icepaybasic_secretcode'    => array('', 'char'),
	    'icepaybasic_paymentmethod' => array('', 'char'),
	    'status_pending'            => array('', 'char'),
	    'status_success'            => array('', 'char'),
	    'status_canceled'           => array('', 'char'),
	    'status_refund'             => array('', 'char'),
	    'status_chargeback'         => array('', 'char'),
	    'payment_logos'             => array('', 'char'),
	    'payment_currency'          => array(0, 'int'),
	    'countries'                 => array(0, 'char'),
	    'min_amount'                => array(0, 'int'),
	    'max_amount'                => array(0, 'int'),
	    'cost_per_transaction'      => array(0, 'int'),
	    'cost_percent_total'        => array(0, 'int'),
	    'tax_id'                    => array(0, 'int')
	);

	$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

	//self::$_this = $this;
    }

    private function _getVersion(){
        return intval(str_replace(".", "", vmVersion::$RELEASE));
    }

    protected function getVmPluginCreateTableSQL() {
	return $this->createTableSQL('Payment ICEPAY Table');
    }

    private function icepay(){
        if (!isset($this->_icepay)) $this->_icepay = new Icepay_Project_Helper();
        return $this->_icepay;
    }

    function getTableSQLFields() {

        $SQLfields = array(
	    'id' => 'tinyint(1) unsigned NOT NULL AUTO_INCREMENT',
	    'virtuemart_order_id' => 'int(11) UNSIGNED DEFAULT NULL',
	    'order_number' => 'char(32) DEFAULT NULL',
	    'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED DEFAULT NULL',
	    'payment_name' => 'char(255) NOT NULL DEFAULT \'\' ',
	    'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\' ',
	    'payment_currency' => 'char(3) ',
	    'cost_per_transaction' => ' decimal(10,2) DEFAULT NULL ',
	    'cost_percent_total' => ' decimal(10,2) DEFAULT NULL ',
	    'tax_id' => 'smallint(11) DEFAULT NULL',
            'icepay_order_id' => 'int(11) UNSIGNED DEFAULT NULL',
            'icepay_transaction_id' => 'char(32) DEFAULT NULL',
            'icepay_status' => 'char(32) DEFAULT \'NEW\''
	);

        foreach($this->icepay()->postback()->getPostbackResponseFields() as $param => $postback){
            $field = strtolower($param);
            $SQLfields["icepay_response_{$field}"] = 'varchar(120) DEFAULT NULL';
        }

	return $SQLfields;
    }

    private function _getLangISO(){
		$lang = &JFactory::getLanguage();
		$arr = explode("-",$lang->get('tag'));
		return strtoupper($arr[0]);
    }

    function plgVmConfirmedOrder($cart, $order) {

	if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
// 		$params = new JParameter($payment->payment_params);
	$lang = JFactory::getLanguage();
	$filename = 'com_virtuemart';
	$lang->load($filename, JPATH_ADMINISTRATOR);
	$vendorId = 0;

	$html = "";

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
	$this->getPaymentCurrency($method);
	// END printing out HTML Form code (Payment Extra Info)
	$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
	$db = &JFactory::getDBO();
	$db->setQuery($q);
	$currency_code_3 = $db->loadResult();
	$paymentCurrency = CurrencyDisplay::getInstance($method->payment_currency);
	$totalInPaymentCurrency = round($paymentCurrency->convertCurrencyTo($method->payment_currency, $order['details']['BT']->order_total, false), 2);
	$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

        $address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);

	$this->_virtuemart_paymentmethod_id = $order['details']['BT']->virtuemart_paymentmethod_id;
        $dbValues['order_number'] = $order['details']['BT']->order_number;
	$dbValues['payment_name'] = $this->renderPluginName($method);
	$dbValues['virtuemart_paymentmethod_id'] = $this->_virtuemart_paymentmethod_id;
	$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
	$dbValues['cost_percent_total'] = $method->cost_percent_total;
	$dbValues['payment_currency'] = $currency_code_3 ;
	$dbValues['payment_order_total'] = $totalInPaymentCurrency;
	$dbValues['tax_id'] = $method->tax_id;
	$dbValues['icepay_order_id'] = $cart->virtuemart_order_id;
	$dbValues['icepay_status'] =  "NEW";
        $this->storePSPluginInternalData($dbValues);

        $amount = $totalInPaymentCurrency * 100;

        $imgURL = JROUTE::_(JURI::root() . 'images/stories/virtuemart/' . $this->_psType . '/icepaybasic_paynow.png');
        $returnURL = JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=icepayresponse&task=result&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id);

        $icepay = $this->_icepay->basic();
//        if ($method->icepaybasic_paymentmethod != "basicmode"){
//            $api = Icepay_Api_Basic::getInstance()->readFolder();
//
//            // Store all paymentmethods in an array
//            $paymentmethods = $api->getArray();
//
//            // new Icepay_Paymentmethod_Ideal()
//            $icepay = new $paymentmethods["ideal"]();
//        }

        try {

            $icepay
                ->setMerchantID($method->icepaybasic_merchantid)
                ->setSecretCode($method->icepaybasic_secretcode);

            $icepay
                ->setAmount($amount)
                ->setCountry(ShopFunctions::getCountryByID($address->virtuemart_country_id, 'country_2_code'))
                ->setLanguage($this->_getLangISO())
                ->setCurrency($currency_code_3)
                ->setReference($order['details']['BT']->order_number)
                ->setDescription($order['details']['BT']->order_number)
                ->setSuccessURL($returnURL)
                ->setErrorURL($returnURL);

            $url = $icepay
                ->setOrderID($cart->virtuemart_order_id)->getURL();

        } catch (Exception $e){
            die ($e->getMessage());
        }



        $html = '<p>'.JText::_('VMPAYMENT_ICEPAYBASIC_CLICK_ON_BUTTON').'<p>';
        $html.= '<form action="' . $url . '" method="post" name="vm_icepay_basic_form" >';
        $html.= '<input type="image" name="submit" src="'.$imgURL.'" alt="'.JText::_('VMPAYMENT_ICEPAYBASIC_PAYNOW').'" />';
	$html.= '</form>';

	$html.= ' <script type="text/javascript">';
	$html.= ' document.vm_icepay_basic_form.submit();';
	$html.= ' </script>';




        //Manuall change the order history since Virtuemart sets the customer notified to 1 by default
//        $db = &JFactory::getDBO();
//        $q = 'UPDATE `#__virtuemart_order_histories` SET customer_notified=0 WHERE virtuemart_order_id = '.$cart->virtuemart_order_id.' ORDER BY virtuemart_order_history_id DESC LIMIT 1  ';
//        $db->setQuery($q);
//        $db->query($q);

        if (!class_exists('VirtueMartModelOrders'))  require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
        if (!class_exists('VirtueMartCart')) require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

        $cart = VirtueMartCart::getCart();
        $modelOrder = new VirtueMartModelOrders();
        $orderitems = $modelOrder->getOrder($cart->virtuemart_order_id);
        //$cart->sentOrderConfirmedEmail($orderitems);

        //Ok so the functions have changed between Virtuemart 2.0.0 and 2.0.2 (great!)
        switch ($this->_getVersion()){
            case 200:
                $return = $this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html); // keep cart, send order
                break;
            default:
                $return = $this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html, $dbValues['payment_name'], $method->status_pending);
        }
        return $return;

    }

    function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}
	 $this->getPaymentCurrency($method);
	$paymentCurrencyId = $method->payment_currency;
    }


    /* Function to handle all responses */
    public function plgVmOnPaymentResponseReceived(  &$html) {

        $virtuemart_paymentmethod_id = JRequest::getInt('pm', 0);

	$vendorId = 0;
	if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
	    return null; // Another method was selected, do nothing
	}
	if (!$this->selectedThisElement($method->payment_element)) {
	    return false;
	}

        $icepay = $_SERVER['REQUEST_METHOD'] == 'POST'?$this->_icepay->postback():$this->_icepay->result();

        try {
            $icepay
                ->setMerchantID($method->icepaybasic_merchantid)
                ->setSecretCode($method->icepaybasic_secretcode);

        } catch (Exception $e){
            echo($e->getMessage());
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            if($icepay->validate()){
                switch ($icepay->getStatus()){
                    case Icepay_StatusCode::OPEN:

                        JRequest::setVar('icepay_msg', Jtext::_('VMPAYMENT_ICEPAYBASIC_MSG_OPEN'));
                        $html = $this->_getPaymentResponseHtml($icepay->getResultData(), $this->renderPluginName($method));
                        // Close the cart
                        $this->emptyCart(null);

                        return true;
                        break;
                    case Icepay_StatusCode::SUCCESS:

                        JRequest::setVar('icepay_msg', Jtext::_('VMPAYMENT_ICEPAYBASIC_MSG_OK'));
                        $html = $this->_getPaymentResponseHtml($icepay->getResultData(), $this->renderPluginName($method));
                        // Close the cart
                        $this->emptyCart(null);

                        return true;
                        break;
                    case Icepay_StatusCode::ERROR:
                        JError::raiseWarning('SOME_ERROR_CODE', JTExt::sprintf('VMPAYMENT_ICEPAYBASIC_MSG_ERR', $icepay->getStatus(true)));
                        return false;
                        break;
                }
            }
        } else {

            $this->_debug = true; // force debug here

            try {
                if($icepay->validate()){

                    // Load order data
                    $payment = $this->getDataByOrderId($icepay->getOrderID());
                    //$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($icepay->getOrderID());
                    $method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);

                    $this->logInfo('Postback received: '.serialize($icepay->getPostback()));


                    if ($payment->icepay_status == "NEW" || $icepay->canUpdateStatus($payment->icepay_status)){

                        $response_fields = array();

                        $copyValue = array(
                            "order_number",
                            "virtuemart_paymentmethod_id",
                            "payment_name",
                            "payment_order_total",
                            "payment_currency",
                            "cost_per_transaction",
                            "cost_percent_total",
                            "tax_id",
                            "cost_percent_total",
                            "cost_percent_total",
                            "cost_percent_total",
                        );

                        foreach ($copyValue as $value) {
                            $response_fields[$value] = $payment->$value;
                        }

                        // get all know columns of the table
                        $db = JFactory::getDBO();
                        $query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
                        $db->setQuery($query);
                        $columns = $db->loadResultArray(0);
                        foreach ($icepay->getPostback() as $key => $value) {
                            $table_key = 'icepay_response_' . strtolower($key);
                            if (in_array($table_key, $columns)) {
                                $response_fields[$table_key] = $value;
                            }
                        }
                        $response_fields['icepay_order_id']         = $icepay->getOrderID();
                        $response_fields['icepay_transaction_id']   = $icepay->getPostback()->transactionID;
                        $response_fields['icepay_status']           = strtoupper($icepay->getStatus());
                        $response_fields['virtuemart_order_id']     = $icepay->getOrderID();

                         switch ($this->_getVersion()){
                            case 200:
                                $this->storePSPluginInternalData($response_fields);
                                break;
                            default:
                                $this->storePSPluginInternalData($response_fields, 'virtuemart_order_id', true);
                        }




                        switch (strtoupper($icepay->getStatus())){
                            case Icepay_StatusCode::OPEN:
                                $new_status = $method->status_pending;
                                break;
                            case Icepay_StatusCode::SUCCESS:
                                $new_status = $method->status_success;
                                break;
                            case Icepay_StatusCode::ERROR:
                                $new_status = $method->status_canceled;
                                break;
                            case Icepay_StatusCode::REFUND:
                                $new_status = $method->status_refund;
                                break;
                            case Icepay_StatusCode::CHARGEBACK:
                                $new_status = $method->status_chargeback;
                                break;
                        }
                        if (!class_exists('VirtueMartModelOrders'))
                            require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

			$modelOrder = new VirtueMartModelOrders();

                        $order = array();
                        $order['order_status'] = $new_status;
                        $order['virtuemart_order_id'] = $icepay->getOrderID();
                        $order['comments'] = JTExt::_($icepay->getTransactionString());
                        $order['customer_notified'] = 1; //Wont send an e-mail though, buggy virtuemart stuff
                        $modelOrder->updateStatusForOneOrder($icepay->getOrderID(), $order, true);

                    }
                } else {
                    if ($icepay->isVersionCheck()){
                        $this->outputVersion($icepay->validateVersion());
                    } else {
                        die(" Unable to validate postback data");
                    }
                }


            } catch (Exception $e){
                echo($e->getMessage());
            }

            //Ensure the view is not created
            exit();
        }



    }
    
    public function getVersions(){
        return sprintf(t("Version %s using PHP API 2 version %s"),$this->_version,$this->_icepay->getVersion());
    }
    
    
    private function outputVersion($extended = false){
        $dump = array(
            "module" => $this->getVersions(),
            "notice" => "Checksum validation passed!"
        );
        if ($extended){
            $dump["additional"] = array(
                "joomla" => JVERSION,
                "virtuemart" => vmVersion::$RELEASE
            );
        } else {
            $dump["notice"] = "Checksum failed! Merchant ID and Secret code probably incorrect.";
        }
        var_dump($dump);
        exit();
    }

    function plgVmOnUserPaymentCancel() {


    }

    /*
     *   plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
     * Return:
     * Parameters:
     *  None
     *  @author Valerie Isaksen
     */

    function plgVmOnPaymentNotification() {



    }

    /**
     * Display stored payment data for an order
     * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
     */
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

	if (!$this->selectedThisByMethodId($payment_method_id)) {
	    return null; // Another method was selected, do nothing
	}

	$db = JFactory::getDBO();
	$q = 'SELECT * FROM `' . $this->_tablename . '` '
		. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
	$db->setQuery($q);

	if (!($paymentTable = $db->loadObject())) {
	   // JError::raiseWarning(500, $db->getErrorMsg());
	    return '';
	}
	$this->getPaymentCurrency($paymentTable);
	$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $paymentTable->payment_currency . '" ';
	$db = &JFactory::getDBO();
	$db->setQuery($q);
	$currency_code_3 = $db->loadResult();
	$html = '<table class="adminlist">' . "\n";
        $html .=$this->getHtmlHeaderBE();
	$html .= $this->getHtmlRowBE('ICEPAYBASIC_PAYMENT_NAME', $paymentTable->payment_name);
	$html .= $this->getHtmlRowBE('ICEPAYBASIC_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total.' '.$currency_code_3);
	$code = "icepay_response_";
	foreach ($paymentTable as $key => $value) {
	    if (substr($key, 0, strlen($code)) == $code && $key != "icepay_response_checksum") {
		$html .= $this->getHtmlRowBE($key, $value);
	    }
	}
	$html .= '</table>' . "\n";
	return $html;

    }


    function _getPaymentResponseHtml($data, $payment_name) {
	$html = '<table>' . "\n";
	$html .= $this->getHtmlRow('ICEPAYBASIC_PAYMENT_NAME', $payment_name);
	$html .= $this->getHtmlRow('ICEPAYBASIC_PAYMENT_STATUS', JRequest::getVar('icepay_msg'));
	$html .= $this->getHtmlRow('ICEPAYBASIC_PAYMENT_REFERENCE', $data->reference);
	$html .= $this->getHtmlRow('ICEPAYBASIC_PAYMENT_TRANSACTIONID', $data->transactionID);
	$html .= '</table>' . "\n";
	return $html;
    }

    function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
	if (preg_match('/%$/', $method->cost_percent_total)) {
	    $cost_percent_total = substr($method->cost_percent_total, 0, -1);
	} else {
	    $cost_percent_total = $method->cost_percent_total;
	}
	return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
    }

    /**
     * Check if the payment conditions are fulfilled for this payment method
     * @author: Valerie Isaksen
     *
     * @param $cart_prices: cart prices
     * @param $payment
     * @return true: if the conditions are fulfilled, false otherwise
     *
     */
    protected function checkConditions($cart, $method, $cart_prices) {


	$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

	$amount = $cart_prices['salesPrice'];
	$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == 0) ));

	$countries = array();
	if (!empty($method->countries)) {
	    if (!is_array($method->countries)) {
		$countries[0] = $method->countries;
	    } else {
		$countries = $method->countries;
	    }
	}
	// probably did not gave his BT:ST address
	if (!is_array($address)) {
	    $address = array();
	    $address['virtuemart_country_id'] = 0;
	}

	if (!isset($address['virtuemart_country_id']))
	    $address['virtuemart_country_id'] = 0;
	if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
	    if ($amount_cond) {
		return true;
	    }
	}

	return false;
    }

    /**
     * We must reimplement this triggers for joomla 1.7
     */

    /**
     * Create the table for this plugin if it does not yet exist.
     * This functions checks if the called plugin is active one.
     * When yes it is calling the standard method to create the tables
     * @author Val�rie Isaksen
     *
     */
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

	return $this->onStoreInstallPluginTable($jplugin_id);
    }

    /**
     * This event is fired after the payment method has been selected. It can be used to store
     * additional payment info in the cart.
     *
     * @author Max Milbers
     * @author Val�rie isaksen
     *
     * @param VirtueMartCart $cart: the actual cart
     * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
     *
     */
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart) {
	return $this->OnSelectCheck($cart);
    }

    /**
     * plgVmDisplayListFEPayment
     * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
     *
     * @param object $cart Cart object
     * @param integer $selected ID of the method selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     *
     * @author Valerie Isaksen
     * @author Max Milbers
     */
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
	return $this->displayListFE($cart, $selected, $htmlIn);
    }

    /*
     * plgVmonSelectedCalculatePricePayment
     * Calculate the price (value, tax_id) of the selected method
     * It is called by the calculator
     * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
     * @author Valerie Isaksen
     * @cart: VirtueMartCart the current cart
     * @cart_prices: array the new cart prices
     * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
     *
     *
     */

    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
	return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }

    /**
     * plgVmOnCheckAutomaticSelectedPayment
     * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     * The plugin must check first if it is the correct type
     * @author Valerie Isaksen
     * @param VirtueMartCart cart: the cart object
     * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
     *
     */
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {
	return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the method-specific data.
     *
     * @param integer $order_id The order ID
     * @return mixed Null for methods that aren't active, text (HTML) otherwise
     * @author Max Milbers
     * @author Valerie Isaksen
     */
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
	  $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }

    /**
     * This event is fired during the checkout process. It can be used to validate the
     * method data as entered by the user.
     *
     * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
     * @author Max Milbers

      public function plgVmOnCheckoutCheckDataPayment($psType, VirtueMartCart $cart) {
      return null;
      }
     */

    /**
     * This method is fired when showing when priting an Order
     * It displays the the payment method-specific data.
     *
     * @param integer $_virtuemart_order_id The order ID
     * @param integer $method_id  method used for this order
     * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
     * @author Valerie Isaksen
     */
    function plgVmonShowOrderPrintPayment($order_number, $method_id) {
	return $this->onShowOrderPrint($order_number, $method_id);
    }

    /**
     * Save updated order data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderPayment(  $_formData) {
      return null;
      }
     */
    /**
     * Save updated orderline data to the method specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this method is not actived.
     * @author Oscar van Eijk

      public function plgVmOnUpdateOrderLine(  $_formData) {
      return null;
      }
     */
    /**
     * plgVmOnEditOrderLineBE
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
      return null;
      }
     */

    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for method that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk

      public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
      return null;
      }
     */
    function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
	return $this->declarePluginParams('payment', $name, $id, $data);
    }

    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
	return $this->setOnTablePluginParams($name, $id, $table);
    }

}

// No closing tag
