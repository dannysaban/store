<?php
/**
 * Controller for the OPC ajax and checkout
 *
 * @package One Page Checkout for VirtueMart 2
 * @subpackage opc
 * @author stAn
 * @author RuposTel s.r.o.
 */
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
jimport('joomla.application.component.controller');
class VirtueMartControllerOpc extends JController {
     /**
     * Construct the cart
     *
     * @access public
     * @author Max Milbers
     */
    public function __construct() {
	parent::__construct();
	{
	    if (!defined('JPATH_OPC')) define('JPATH_OPC', JPATH_SITE.DS.'components'.DS.'com_onepage'); 
	    if (!class_exists('VirtueMartCart'))
		require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	    if (!class_exists('calculationHelper'))
		require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		
		
		
	}
	}
	
	function getEmail($id)
	{
	    $user =& JFactory::getUser();
		return $user->email; 
	    if(!class_exists('VirtuemartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
	    $user = new VirtueMartModelUser;
		//$user->setCurrent();
		$d = $user->getUser();
		return $d->JUser->get('email');
	}
	function setAddress(&$cart, $ajax=false)
	{
	   $post = JRequest::get('post'); 
	   
	   $cart->prepareAddressDataInCart('BT', 1);
	 
	     if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
		 $corefields = VirtueMartModelUserfields::getCoreFields();
		 $fieldtype = 'BTaddress';
		 $userFields = $cart->$fieldtype;
		
		 $cart->prepareAddressDataInCart('ST', 1);
		 $fieldtype = 'STaddress';
		 $userFieldsst = $cart->$fieldtype;

		
		$db=&JFactory::getDBO();
		
		// we will populate the data for logged in users
		if (!empty($post['ship_to_info_id']))
		{
		 
		 
		
		  // this part for registered users, let's retrieve the selected address
		 
		  $q = "select * from #__virtuemart_userinfos where virtuemart_userinfo_id = '".$db->getEscaped($post['ship_to_info_id'])."' limit 0,1"; 
		  $db->setQuery($q); 
		  $res = $db->loadAssoc(); 
		  $err = $db->getErrorMsg(); 
		 
		  $user_id = $res['virtuemart_user_id']; 
		  JRequest::setVar('shipto', $_POST['ship_to_info_id']);
		  $cart->selected_shipto = $db->getEscaped($post['ship_to_info_id']); 
		  
		  $email = $this->getEmail($user_id); 
		  if (!empty($email)) $post['email'] = $email; 
		  
		  if (false)
		  {
		  // updated on 4th may 2012
		  if (!empty($res))
		  foreach ($res as $k=>$line)
		   {
		     // rewrite POST only when empty
		     if (empty($post[$k]))
		     $post[$k] = $line; 
			 // we will also set this for rest of ajax
		   }
		   // the selected address is BT
		   // we need to set STsameAsBT
		   // delete any ST address if found
		   }
			 
			  foreach ($userFields['fields'] as $key=>$uf22)   
				{
				 // don't save passowrds
				 if (stripos($uf22['name'], 'password')) $post[$uf22['name']] = ''; 
				 
				 // POST['variable'] and POST['shipto_variable'] are prefered from database information
				 if (!empty($post[$uf22['name']]) || ((($res['address_type'] == 'ST') && (!empty($post['shipto_'.$uf22['name']])))))
					{
					    // if the selected address is ST, let's first checkout POST shipto_variable
						// then POST['variable']
						// and then let's insert it from the DB
					    if (($res['address_type'] == 'ST') && (!empty($post['shipto_'.$uf22['name']])))
						$address[$uf22['name']] = $post['shipto_'.$uf22['name']]; 
						else
						$address[$uf22['name']] = $post[$uf22['name']]; 
					}
					else
					{
					   if (!empty($res[$uf22['name']]))
					   $address[$uf22['name']] = $res[$uf22['name']]; 
					   else $address[$uf22['name']] = ''; 
					}
				}
				
		 // the selected is BT
		 if ($res['address_type'] == 'BT') 
		    {
			    $cart->STsameAsBT = 1; 
				$cart->BT = $address; 
				$cart->ST = 0; 
				/*
				 foreach ($res as $keybt2=>$val2)
				 {
				   $cart->BT = array(); 
				   $cart->BT[$keybt2] = $val2; 
				 }
				 */
				 return;
			}
			else 
			{
			 $cart->ST = $address; 
			 $cart->STsameAsBT = 0; 
			}
			
			// the selected address is not BT
			// we need to get a proper BT
			// and set up found address as ST
			if ((!$cart->STsameAsBT))
			{
				$q = "select * from #__virtuemart_userinfos where virtuemart_user_id = '".$db->getEscaped($res['virtuemart_user_id'])."' and address_type = 'BT' limit 0,1"; 
				$db->setQuery($q); 
				$btres = $db->loadAssoc();

				 $cart->prepareAddressDataInCart('BT', 1);
				 $fieldtype = 'BTaddress';
				 $userFieldsbt = $cart->$fieldtype;
				foreach ($userFieldsbt['fields'] as $key=>$uf)   
				{
				 // POST['variable'] is prefered form userinfos.variable in db
				 $index = str_replace('shipto_', '', $uf['name']); 
				 if (!empty($post[$index]))
					{
						$address[$index] = $post[$index]; 
					}
					else
					{
					   $address[$index] = $btres[$index]; 
					}
				}
				$cart->BT = $address; 
				
				//var_dump($cart->BT); 
				//var_dump($cart->ST); 
				//die();
				 return;
			}
			
		}
		
		// unlogged users get data from the form BT address
		foreach ($userFields['fields'] as $key=>$uf33)   
		 {
		   if (!empty($post[$uf33['name']]))
		    {
			  $address[$uf33['name']] = $post[$uf33['name']]; 
			}
		 }
		 if (!empty($address))
		 $cart->BT = $address; 
		 
		 // ST address for unlogged
		 $address = array(); 
		 foreach ($userFieldsst['fields'] as $key=>$uf44)   
		 {
		   if (!empty($post['shipto_'.$uf44['name']]))
		    {
			  $address[$uf44['name']] = $post['shipto_'.$uf44['name']]; 
			}
		 }
		  if (!empty($address))
		 $cart->ST = $address; 
		 
		 
		 

	}
	
	function setAddress2(&$cart)
	{
	  $address = array(); 
	  $address['virtuemart_country_id'] = JRequest::getInt('virtuemart_country_id', 0); 
	  $address['zip'] = JRequest::getVar('zip', ''); 
	  $address['virtuemart_state_id'] = JRequest::getInt('virtuemart_state_id', ''); 
	  $address['address_1'] = JRequest::getVar('address_1', ''); 
	  $address['address_2'] = JRequest::getVar('address_2', ''); 
	  $cart->ST = $address; 
	  // not used $ship_to_info_id = JRequest::getVar('ship_to_info_id'); 
	}	
	function checkout()
	{
	
	  // password modification in OPC
	  $pwd = JRequest::getVar('opc_password', '', 'post', 'string', JREQUEST_ALLOWRAW); 
	  if (!empty($pwd)) 
	   {
	     JRequest::setVar('password', $pwd); 
		 // raw
		 $_POST['password'] = $pwd; 
	   }
	  // register user first: 
	  $reg = JRequest::getVar('register_account'); 
	  if (empty($reg)) $reg = false; 
	  else $reg = true; 
	  
	  $this->runExt(); 
	  
	  
	  //if (!class_exists('VirtueMartControllerUser'))
	  //require_once(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'controllers'.DS.'user.php'); 
	  //$userC = new VirtueMartControllerUser(); 
	  $cart =& VirtueMartCart::getCart(false);
	  $this->saveData($cart,$reg); 
	  
	  $cart->virtuemart_paymentmethod_id = JRequest::getInt('virtuemart_paymentmethod_id', ''); 
	  if (!class_exists('VirtueMartControllerCartOpc'))
	  require_once(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'overrides'.DS.'cartcontroller.php'); 
	  $cartcontroller = new VirtueMartControllerCartOpc(); 
	  
	  $cart->virtuemart_shipmentmethod_id = JRequest::getInt('virtuemart_shipmentmethod_id', ''); 
	  
	  $cartcontroller->setshipment($cart); 
	  
	  $cartcontroller->setpayment($cart); 
	 
	  // security: 
	  JRequest::setVar('html', ''); 
	 
	  $this->setAddress($cart); 
	  $post = JRequest::get('post'); 
	  
	  require_once(JPATH_OPC.DS.'overrides'.DS.'cart_override.php'); 
	  $OPCcheckout = new OPCcheckout($cart); 
	
	  if (!VmConfig::get('agree_to_tos_onorder',0))
	  {
	    if (!empty($post['tosAccepted']))
		{
	     $cart->tosAccepted = 1; 
		 $cart->BT['agreed'] = 1; 
		 if (!empty($cart->ST)) $cart->ST['agreed'] = 1; 
		}
		else
		{
		 
		}
	  }
	  if (empty($cart->BT)) 
	   {
	   
	   }
	  
	  $OPCcheckout->checkoutData($cart, $OPCcheckout); 
	  
	  if ($cart->_dataValidated)
		{
	  $cart->_confirmDone = true;
	 // echo 'OK';
	   $output =  $OPCcheckout->confirmedOrder($cart, $this);
	
		}
		else 
		{
			$mainframe = JFactory::getApplication();
			$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart'), JText::_('COM_VIRTUEMART_CART_CHECKOUT_DATA_NOT_VALID'));

		}
	
	  //$post = JRequest::get('post');
		$mainframe =& JFactory::getApplication();		  
	  			$pathway =& $mainframe->getPathway();
		$document = JFactory::getDocument();
	  
	//  $html = JRequest::getVar('html', JText::_('COM_VIRTUEMART_ORDER_PROCESSED'), 'post', 'STRING', JREQUEST_ALLOWRAW);
	  $pathway->addItem(JText::_('COM_VIRTUEMART_CART_THANKYOU'));
	  $document->setTitle(JText::_('COM_VIRTUEMART_CART_THANKYOU'));
	  $cart->setCartIntoSession(); 
	  // now the plugins should have already loaded the redirect html
	  // we can safely 
	  $virtuemart_order_id = $cart->virtuemart_order_id; 
	   
	      if ($virtuemart_order_id) {
			if (!class_exists('VirtueMartCart'))
			    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
			// get the correct cart / session
			//$cart = VirtueMartCart::getCart();
			
			// send the email ONLY if payment has been accepted
			if (!class_exists('VirtueMartModelOrders'))
			    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
			$order = new VirtueMartModelOrders();
			$orderitems = $order->getOrder($virtuemart_order_id);
			if (method_exists($cart, 'sentOrderConfirmedEmail'))
			{
			  //$cart->sentOrderConfirmedEmail($orderitems);

			}
			//We delete the old stuff

			//$cart->emptyCart();
		    }

	   JRequest::setVar('view', 'cart'); 
	  $_REQUEST['view'] = 'cart'; 
	  $_POST['view'] = 'cart'; 
	  $_GET['view'] = 'cart'; 


	 
	  $view = $this->getView('cart', 'html');
	  $view->assignRef('html', $output); 
	  $view->setLayout('order_done');
	    // Display it all
	   $view->display();
	  
	}
	
	// support for non-standard extensions
	// will be changed in the future over OPC extension tab and API
	function runExt()
	{
	  
	// support for USPS: 
	$shipping_method = JRequest::getVar('saved_shipping_id', ''); 
	if (stripos($shipping_method, 'usps_')!==false)
	 {
	   $data = JRequest::getVar($shipping_method.'_extrainfo', ''); 
	   
	  
	  
	   if (!empty($data))
	    {
		  $data = @base64_decode($data);  
		
	   
		  // example data-usps='{"service":"Parcel Post","rate":15.09}'
		  $data = @json_decode($data, true); 
		   //var_dump($data); var_dump($shipmentid); //usps_id_0
		  if (!empty($data))
		   {
		     JRequest::setVar('usps_name', (string)$data['service']); 
			 JRequest::setVar('usps_rate', (float)$data['rate']);
			
			 
			 
			 
		   }
		}
	 }
	 // end support USPS
	 	// support for UPS: 
	$shipping_method = JRequest::getVar('saved_shipping_id', ''); 
	if (stripos($shipping_method, 'ups_')!==false)
	 {
	   $data = JRequest::getVar($shipping_method.'_extrainfo', ''); 
	   
	  
	  
	   if (!empty($data))
	    {
		  $data = @base64_decode($data);  
		
	   
		  // example data-usps='{"service":"Parcel Post","rate":15.09}'
		  $data = @json_decode($data, true); 
		  //{"id":"03","code3":"USD","rate":8.58,"GuaranteedDaysToDelivery":[]}
		   //var_dump($data); var_dump($shipmentid); //usps_id_0
		  if (!empty($data))
		   {
		     //JRequest::setVar('ups_name', (string)$data['service']); 
			 JRequest::setVar('ups_rate', $data['id']);
			
			 
			 
			 
		   }
		}
	 }
	 // end support UPS
	}
	function opc()
	{
       /// load shipping here
	   $vars = JRequest::get('post'); 
	   
	   $view = $this->getView('cart', 'html');
	   if (!defined('JPATH_OPC')) define('JPATH_OPC', JPATH_SITE.DS.'components'.DS.'com_onepage'); 
	   require_once(JPATH_OPC.DS.'helpers'.DS.'loader.php'); 
	   require_once(JPATH_OPC.DS.'helpers'.DS.'ajaxhelper.php'); 
	   $OPCloader = new OPCloader; 
	   $cart = VirtueMartCart::getCart(false);
	   $this->setAddress($cart, true); 
	   $cart->prepareCartViewData();
	   $view->cart = $cart; 
	   $view->assignRef('cart', $cart); 
	   @header('Content-Type: text/html; charset=utf-8');
	   @header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	   @header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		
	   $shipping = $OPCloader->getShipping($view, $cart, true); 
	   
	   $return = array(); 
	   $return['shipping'] = $shipping; 
	   // get payment html
	   $ph = '<div style="payment_inner_html">';
	   $ph .= $OPCloader->getPayment($view); 
	   $ph .= '</div>'; 
	   $return['payment'] = $ph;
	   echo json_encode($return); 
	   //echo $shipping;
	   $cart->virtuemart_shipmentmethod_id = 0; 
	   $cart->virtuemart_paymentmethod_id = 0; 
	   $cart->setCartIntoSession();
	   
	}
	/**
	 * Save the user info. The saveData function dont use the userModel store function for anonymous shoppers, because it would register them.
	 * We make this function private, so we can do the tests in the tasks.
	 *
	 * @author Max Milbers
	 * @author Valérie Isaksen
	 *
	 * @param boolean Defaults to false, the param is for the userModel->store function, which needs it to determin how to handle the data.
	 * @return String it gives back the messages.
	 */
	function saveData($cart=false,$register=false) {

	include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
	
	$mainframe = JFactory::getApplication();
		$currentUser = JFactory::getUser();
		$msg = '';
		
		$data = JRequest::get('post');
		

		if (empty($data['address_type'])) $data['address_type'] = 'BT'; 
		

		
		$r = JRequest::getVar('register_account', ''); 
		if (!empty($r) || (VmConfig::get('oncheckout_only_registered', 0)))
		$register = true; 
		
// 		vmdebug('$currentUser',$currentUser);
		if($currentUser->id!=0 || $register){
			$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
			
			//require_once(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'overrides'.DS.'usermodel.php'); 
			
			$userModel = $this->getModel('user');
			//$userModel = new OPCUsermodel(); 
			
			if(!$cart){
				// Store multiple selectlist entries as a ; separated string
				if (key_exists('vendor_accepted_currencies', $data) && is_array($data['vendor_accepted_currencies'])) {
					$data['vendor_accepted_currencies'] = implode(',', $data['vendor_accepted_currencies']);
				}

				$data['vendor_store_name'] = JRequest::getVar('vendor_store_name','','post','STRING',JREQUEST_ALLOWHTML);
				$data['vendor_store_desc'] = JRequest::getVar('vendor_store_desc','','post','STRING',JREQUEST_ALLOWHTML);
				$data['vendor_terms_of_service'] = JRequest::getVar('vendor_terms_of_service','','post','STRING',JREQUEST_ALLOWHTML);
			}
			$data['user_is_vendor'] = 0; 

			
			
			
			//It should always be stored, stAn: it will, but not here
			if($currentUser->id==0 || (empty($data['ship_to_info_id']))){
		if (empty($data['shipto_email'])) $data['shipto_email'] = $data['email']; 
		
		// check for duplicit registration feature
		if ($allow_duplicit)
		{
		  // set the username if appropriate
		  if (empty($data['username']))
			{
			  $username = $data['email']; 
			  $email = $data['email']; 
			}
			else 
			{
			$username = $data['username'];
			if (!empty($data['email'])) $email = $data['email']; 
			else 
			 {
			   // support for 3rd party exts
			   if (strpos($username, '@')!==false)
			    $email = $username; 
			 }
			}
			$db =& JFactory::getDBO(); 
			if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) {
			$q = "select * from #__users where email LIKE '".$db->escape($email)."' limit 0,1"; //or username = '".$db->escape($username)."' ";
			}
			else
			$q = "select * from #__users where email LIKE '".$db->getEscaped($email)."' limit 0,1"; //or username = '".$db->escape($username)."' ";
			$db->setQuery($q); 
			$res = $db->loadAssoc(); 
			$is_dup = false; 
			if (!empty($res))
			 {
			 
			   //ok, the customer already used the same email address
			   $is_dup = true; 
			   $duid = $res['id']; 
			   $GLOBALS['is_dup'] = $duid; 
			   $data['address_type'] = 'BT';
			   $data['virtuemart_user_id'] = $duid; 
			   $data['shipto_virtuemart_user_id'] = $duid; 
			   $this->saveToCart($data);
			   // we will not save the user into the jos_virtuermart_userinfos
			   return true; 
			   
			   // ok, we've got a duplict registration here
			   
			   if (!empty($data['password']) && (!empty($data['username'])))
			    {
				 // if we showed the password fields, let try to log him in 
				 
				  // we can try to log him in if he entered password
				  $credentials = array('username'  => $username,
							'password' => $data['password']);
								
				// added by stAn, so we don't ge an error
				$options = array('silent' => true );
				$mainframe =& JFactory::getApplication(); 
				ob_start();
				$ret = $mainframe->login( $credentials, $options );
				$xxy = ob_get_clean();
				unset($xxy); 
				if ($ret === false)
				 {
				  // the login was not sucessfull
				 }
				 else
				 {
				  // login was sucessfull
				  $dontproceed = true; 
				 }

				}
				// did he check: shipping address is different?
			   $cart->prepareAddressDataInCart('BT', 1);
	 
				if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
				$corefields = VirtueMartModelUserfields::getCoreFields();
				$fieldtype = 'BTaddress';
				$userFields = $cart->$fieldtype;
				$cart->prepareAddressDataInCart('ST', 1);
				$fieldtype = 'STaddress';
				$userFieldsst = $cart->$fieldtype;
				
				if ((!empty($data['sa'])) && ($data['sa'] == 'adresaina'))
				{
				 // yes, his data are in the shipto_ fields
				 $address = array(); 
				 foreach ($data as $ksa=>$vsa)
				  {
				    if (strpos($ksa, 'shipto_')===0)
					$address[$ksa] = $vsa; 
				  }
				}
				else
				{
				 // load the proper BT address
				 $q = "select * from #__virtuemart_userinfos where virtuemart_user_id = '".$duid."' and address_type = 'BT' limit 0,1"; 
				 $db->setQuery($q); 
				 $bta = $db->loadAssoc(); 
				 if (!empty($bta))
				 {
				 $address = array(); 
				 // no, his data are in the BT address and therefore we need to copy them and set a proper BT address
				 foreach ($userFieldsst['fields'] as $key=>$uf)   
				  {
				   $uf['name'] = str_replace('shipto_', '', $uf['name']); 
				   // POST['variable'] is prefered form userinfos.variable in db
				   if (empty($bta[$uf['name']])) $bta[$uf['name']] = ''; 
					{
					  if (!isset($data[$uf['name']])) $data[$uf['name']] = ''; 
					  if (empty($data['address_type_name'])) $data['address_type_name'] = JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_LBL');
					  if (empty($data['name'])) $data['name'] = $bta[$uf['name']];
					  JRequest::setVar('shipto_'.$uf['name'], $data[$uf['name']], 'post'); 
					  // this will set the new BT address in the cart later on and in the order details as well
					  if (!empty($bta[$uf['name']]))
					  JRequest::setVar($uf['name'], $bta[$uf['name']], 'post'); 
					  $address['shipto_'.$uf['name']] = $data[$uf['name']]; 
					}
					
				  }
				  }
				  }
				  // ok, we've got the ST addres here, let's check if there is anything similar
				  $q = "select * from #__virtuemart_userinfos where virtuemart_user_id = '".$duid."'"; 
				  $db->setQuery($q); 
				  $res = $db->loadAssocList(); 
				  $ign = array('virtuemart_userinfo_id', 'virtuemart_user_id', 'address_type', 'address_type_name', 'name', 'agreed', '', 'created_on', 'created_by', 'modified_on', 'modified_by', 'locked_on', 'locked_by');  
				  if (function_exists('mb_strtolower'))
				  $cf = 'mb_strtolower'; 
				  else $cf = 'strtolower'; 
				  $e = $db->getErrorMsg(); 
				  
				  if (!empty($res))
				  {
				  foreach ($res as $k=>$ad)
				   {
				     $match = false; 
				     foreach ($ad as $nn=>$val)
					  {
					    if (!in_array($nn, $ign))
						{
						  
						  
						  if (!isset($address['shipto_'.$nn])) $address['shipto_'.$nn] = ''; 
						  if ($cf($val) != $cf($address['shipto_'.$nn])) { $match = false; break; }
						  else { $match = true; 
						    $lastuid = $ad['virtuemart_userinfo_id']; 
							$lasttype = $ad['address_type']; 
						  }
						}
					  }
					  if (!empty($match))
					   {
					    // we've got a ST address already registered
						if ($lasttype == 'BT')
						 {
						   // let's set STsameAsBT
						   JRequest::setVar('sa', null); 
						   	
						   // we don't have to do anything as the same data will be saved
							
						   
						 }
						 else
						 {
						   
						   JRequest::setVar('shipto_virtuemart_userinfo_id', $lastuid);
						   $new_shipto_virtuemart_userinfo_id = $lastuid;
						   
						 }
						 break; 
					   }
					  
					  
				   }
				   	if (empty($match) || (!empty($new_shipto_virtuemart_userinfo_id)))
					   {
					   
					     // we need to store it as a new ST address
						 $address['address_type'] = 'ST'; 
						 $address['virtuemart_user_id'] = $duid; 
						 $address['shipto_virtuemart_user_id'] = $duid; 
						 if (empty($new_shipto_virtuemart_userinfo_id))
						 {
						 $address['shipto_virtuemart_userinfo_id'] = 0; 
						 $address['shipto_virtuemart_userinfo_id'] = $this->OPCstoreAddress($address, $duid); 
						 // let's set ST address here
						 }
						 else 
						 $address['shipto_virtuemart_userinfo_id'] = $new_shipto_virtuemart_userinfo_id;
						 $cart->saveAddressInCart($address, 'ST');
						 $btdata = JRequest::get('post'); 
						 $btdata['virtuemart_user_id'] = $duid;
						 $btdata['address_type'] = 'BT'; 
						 $cart->saveAddressInCart($btdata, 'BT');
						 
						 return;
					   }

				  
				 }
				 
				

				
				
			 }
			
			
		}
		if (empty($dontproceed))
		{
			if (empty($data['username']))
			{
			  $data['username'] = $data['email']; 
			}
			if (empty($data['password']) && (!VmConfig::get('oncheckout_show_register', 0)))
			{
			
			$data['password'] = $data['password2'] = uniqid(); 			
			}
			
			$data['name'] = $data['first_name'].' '.$data['last_name']; 

			if (empty($_POST['name']))
			 {
			   $_POST['name'] = $data['name']; 
			 }
			 // Bind the post data to the JUser object and the VM tables, then saves it, also sends the registration email
			if (empty($unlog_all_shoppers))
            $data['guest'] = 0; 
			$ret = $userModel->store($data);
			
			$data['address_type'] = 'ST'; 
			
			$userModel->storeAddress($data);
		
			$user = $ret['user']; 
			$ok = $ret['success']; 
			
			// we will not send this again
			if (empty($unlog_all_shoppers))
			if($currentUser->id==0){
				$msg = (is_array($ret)) ? $ret['message'] : $ret;
				$usersConfig = &JComponentHelper::getParams( 'com_users' );
				$useractivation = $usersConfig->get( 'useractivation' );
				
				
				
				
				if (is_array($ret) && $ret['success'] && !$useractivation) {
					// Username and password must be passed in an array
					$credentials = array('username' => $ret['user']->username,
			  					'password' => $ret['user']->password_clear
					);
					$options = array('silent' => true );
					$return = $mainframe->login($credentials, $options);
				}
			}
			}
		  }

		}
		
		$data['address_type'] = 'BT'; 
		$this->saveToCart($data);
		
		return $msg;
	}
	// this is an overrided function to support duplict emails
	// the orginal function was in: user.php storeAddress($data)
	function OPCstoreAddress($data, $user_id=0)
	{
		  //$user =JFactory::getUser();
		  $this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		  $userModel = $this->getModel('user');

	      $userinfo   = $userModel->getTable('userinfos');
		  if($data['address_type'] == 'BT'){
			$userfielddata = VirtueMartModelUser::_prepareUserFields($data, 'BT');

			if (!$userinfo->bindChecknStore($userfielddata)) {
				vmError('storeAddress '.$userinfo->getError());
			}
		}
		// Check for fields with the the 'shipto_' prefix; that means a (new) shipto address.
		if(isset($data['shipto_virtuemart_userinfo_id'])){
			$dataST = array();
			$_pattern = '/^shipto_/';

			foreach ($data as $_k => $_v) {
				if (preg_match($_pattern, $_k)) {
					$_new = preg_replace($_pattern, '', $_k);
					$dataST[$_new] = $_v;
				}
			}

			$userinfo   = $userModel->getTable('userinfos');
			if(isset($dataST['virtuemart_userinfo_id']) and $dataST['virtuemart_userinfo_id']!=0){
				if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'permissions.php');
				if(!Permissions::getInstance()->check('admin')){

					$userinfo->load($dataST['virtuemart_userinfo_id']);
					/*
					$user = JFactory::getUser();
					if($userinfo->virtuemart_user_id!=$user->id){
						vmError('Hacking attempt as admin?','Hacking attempt');
						return false;
					}
					*/
				}
			}

			if(empty($userinfo->virtuemart_user_id)){
				if(isset($data['virtuemart_user_id'])){
					$dataST['virtuemart_user_id'] = $data['virtuemart_user_id'];
				} else {
					//Disadvantage is that admins should not change the ST address in the FE (what should never happen anyway.)
					$dataST['virtuemart_user_id'] = $user_id;
				}
			}

			$dataST['address_type'] = 'ST';
			$userfielddata = VirtueMartModelUser::_prepareUserFields($dataST, 'ST');

			if (!$userinfo->bindChecknStore($userfielddata)) {
				vmError($userinfo->getError());
			}
		}


		return $userinfo->virtuemart_userinfo_id;
		
	}
	function sendRegistrationMail($user)
	{
	
	  // Compile the notification mail values.
		$data = $user->getProperties();
		$config	= JFactory::getConfig();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::base();
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$useractivation = $usersConfig->get( 'useractivation' );
		// Handle account activation/confirmation emails.
		if ($useractivation == 2)
		{
			// Set the link to confirm the user email.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);
		}
		elseif ($useractivation == 1)
		{
			// Set the link to activate the user account.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);
		} else {

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}

		// Send the registration email.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

	}
	
		/**
	 * This function just gets the post data and put the data if there is any to the cart
	 *
	 * @author Max Milbers
	 *
	 * this is from user model 
	 */
	function saveToCart($data){
 
		if(!class_exists('VirtueMartCart')) require(JPATH_VM_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart();
		$cart->saveAddressInCart($data, $data['address_type']);
		
		$sa = JRequest::getVar('sa', ''); 
		if ($sa == 'adresaina')
		$cart->saveAddressInCart($data, 'ST');
		else $cart->STsameAsBT = 1; 
		$cart->setCartIntoSession();
	}

    
}