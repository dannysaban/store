<?php

/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/

if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'helpers'.DS.'ajaxhelper.php'); 
require_once(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'overrides'.DS.'virtuemart.cart.view.html.php'); 
require_once(JPATH_VM_ADMINISTRATOR.DS.'version.php'); 
class OPCloader extends VirtueMartViewCart {

 function getName()
 {
   return 'OPC'; 
 }
 
 // returns the domain url ending with slash
 function getUrl($rel = false)
 {
   $url = JURI::root(); 
   if ($rel) $url = JURI::root(true);
   if (empty($url)) return '/';    
   if (substr($url, strlen($url)-1)!='/')
   $url .= '/'; 
   return $url; 
 }
 
 function getReturnLink(&$ref)
 {
 
   return base64_encode($this->getUrl().'index.php?option=com_virtuemart&view=cart');
   /*
   if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) {
  
   }
   else
   {
    return base64_encode(JURI::root().'/index.php?option=com_virtuemart&page=cart');
   }
   */
 }
 function getTosLink(&$ref)
 {
 $x = VmVersion::$RELEASE;
 if (!version_compare($x, '2.0.2', '>=')) return ""; 
 $cart = $ref->cart; 
 
 $tos_link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $cart->vendor->virtuemart_vendor_id.'&tmpl=component');
			if (strpos($tos_link, 'http')!==0)
			 {
			   $base = JURI::base(); 
			   if (substr($base, -1)=='/') $base = substr($base, 0, -1);
			   
			   if (substr($tos_link, 0, 1)!=='/') $tos_link = '/'.$tos_link; 
			   
			   $tos_link = $base.$tos_link; 
			   //var_dump($tos_link); 
			 }
			 return $tos_link;
 } 
 function getFormVars($ref)
 {
   return '<input type="hidden" value="com_virtuemart" name="option" id="opc_option" />
		<input type="hidden" value="checkout" name="task" id="opc_task" />
		<input type="hidden" value="opc" name="view" id="opc_view" />
		<input type="hidden" name="saved_shipping_id" id="saved_shipping_id" value=""/>
		<input type="hidden" value="opc" name="controller" id="opc_controller" />'.JHTML::_( 'form.token' ); 
		
 }
 
    // input parameters: STaddress or BTaddress fields
    function setCountryAndState(&$address)
	{
	  
	  if (!class_exists('ShopFunctions'))
	  require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
	  
	  if ((isset($address) && (!is_object($address))) || ((!is_object($address)) && (empty($address->virtuemart_country_id))))
	  {
	  
	  if (!empty($address['virtuemart_country_id']) && (!empty($address['virtuemart_country_id']['value'])) && (((is_numeric($address['virtuemart_country_id']['value'])))))
	   {
	     $address['virtuemart_country_id']['value'] = shopFunctions::getCountryByID($address['virtuemart_country_id']['value']); 
	   }
	  else 
	  {
	  $address['virtuemart_country_id']['value'] = ''; 
	  }
	   
	  if (!empty($address['virtuemart_state_id']) && (!empty($address['virtuemart_state_id']['value'])) && ((is_numeric($address['virtuemart_state_id']['value']))))
	   {
	     $address['virtuemart_state_id']['value'] = shopFunctions::getStateByID($address['virtuemart_state_id']['value']); 
	   }
	  else $address['virtuemart_state_id']['value'] = ''; 
	  }
	  else
	  {
	  if (!empty($address->virtuemart_country_id) && (((is_numeric($address->virtuemart_country_id)))))
	   {
	     $address->virtuemart_country_id = shopFunctions::getCountryByID($address->virtuemart_country_id); 
	   }
	  else $address->virtuemart_country_id = ''; 
	   
	  if (!empty($address->virtuemart_state_id)  && ((is_numeric($address->virtuemart_state_id))))
	   {
	     $address->virtuemart_state_id = shopFunctions::getStateByID($address->virtuemart_state_id); 
	   }
	  else $address->virtuemart_state_id = ''; 
	  
	  }
	}
	
 	function getUserInfoBT(&$ref)
			{
			
			if (!class_exists('VirtuemartModelUser'))
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
			$umodel = new VirtuemartModelUser();
			$uid = JFactory::getUser()->id;
		    $userDetails = $umodel->getUser();
			$virtuemart_userinfo_id = $umodel->getBTuserinfo_id();
			
							
			$userFields = $umodel->getUserInfoInUserFields('edit', 'BT', $virtuemart_userinfo_id);
			
			
				$db =& JFactory::getDBO(); 
				$q = "select * from #__virtuemart_userinfos as uu, #__users as ju where uu.virtuemart_user_id = '".$uid."' and ju.id = uu.virtuemart_user_id and uu.address_type = 'BT' limit 0,1 "; 
				$db->setQuery($q); 
				$fields = $db->loadAssoc(); 
				//		echo $db->getErrorMsg();
			if (!empty($virtuemart_userinfo_id) && (!empty($userFields[$virtuemart_userinfo_id])))
			//$ref->cart->BTaddress = $userFields[$virtuemart_userinfo_id]['fields']; 
			    // ok, the user is logged, in but his data might not be in $ref->cart->BT[$BTaddress[$k]['name']]
			    $ref->cart->prepareAddressDataInCart('BTaddress', 0);
			    $BTaddress = $ref->cart->BTaddress['fields']; 
				
				$useSSL = VmConfig::get('useSSL', 0);
				$edit_link = JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT&virtuemart_userinfo_id='.$virtuemart_userinfo_id.'&cid[]='.$uid, true, $useSSL);
				
				foreach ($BTaddress as $k=>$val)
				 {
				   
				   //if (empty($BTaddress[$k]['value']) && (!empty($ref->cart->BT)) && (!empty($ref->cart->BT[$BTaddress[$k]['name']]))) $BTaddress[$k]['value'] = $ref->cart->BT[$BTaddress[$k]['name']]; 
				   
				   $BTaddress[$k]['value'] = $fields[$val['name']]; //trim($BTaddress[$k]['value']); 
				   if ($val['name'] == 'agreed') unset($BTaddress[$k]);
				   if ($val['name'] == 'username') unset($BTaddress[$k]);
				   if ($val['name'] == 'password') unset($BTaddress[$k]);
				   
				 }
				 $this->setCountryAndState($BTaddress); 
				 
				 
				$html = $this->fetch($this, 'customer_info.tpl', array('BTaddress' => $BTaddress, 'virtuemart_userinfo_id' => $virtuemart_userinfo_id, 'edit_link' => $edit_link)); 
				if (empty($op_disable_shipto))
				{
				  $html .= '<input type="hidden" name="ship_to_info_id" value="'.$virtuemart_userinfo_id.'" checked="checked" />'; 
				}
				return $html; 
			}
			
	function getUserInfoST($ref)
			{
			   $ref->cart->prepareAddressDataInCart('ST', 1);
			   if (!empty($ref->cart->ST))
			   {
			   
			    $STaddress = $ref->cart->STaddress['fields']; 
				
				foreach ($STaddress as $k=>$val)
				 {
				   
				   $kk = str_replace('shipto_', '', $STaddress[$k]['name']); 
				   if (empty($STaddress[$k]['value']) && (!empty($ref->cart->ST)) && (!empty($ref->cart->ST[$kk]))) $STaddress[$k]['value'] = $ref->cart->ST[$kk]; 				
				   $STaddress[$k]['value'] = trim($STaddress[$k]['value']); 
				   if ($val['name'] == 'agreed') unset($STaddress[$k]);
				   
				 }
				 $this->setCountryAndState($STaddress); 
				 
				}
				else $STaddress = array(); 
				//$bt_user_info = $ref->cart->BTaddress->user_infoid; 
			
				
				if (!class_exists('VirtuemartModelUser'))
				require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
				$umodel = new VirtuemartModelUser();
				
				$virtuemart_userinfo_id = 0; 
				$currentUser =& JFactory::getUser();
				$uid = $currentUser->get('id');
				
			
				
				$userDetails = $umodel->getUser();
				$virtuemart_userinfo_id = $umodel->getBTuserinfo_id();
				
				$userFields = $umodel->getUserInfoInUserFields('default', 'BT', $virtuemart_userinfo_id);
				
				/*
				if (empty($userFields[$virtuemart_userinfo_id]))
				$virtuemart_userinfo_id = $umodel->getBTuserinfo_id();
				else $virtuemart_userinfo_id = $userFields[$virtuemart_userinfo_id]; 
				*/
				
				
				//$id = $umodel->getId(); 
				
				if (empty($virtuemart_userinfo_id)) return false; 
				
				$STaddressList = $umodel->getUserAddressList($uid , 'ST');
				$BTaddress = $ref->cart->BTaddress['fields']; 
				$x = VmVersion::$RELEASE;	
				$useSSL = VmConfig::get('useSSL', 0);
				foreach ($STaddressList as $ke => $address)
				 {
				   
				   $this->setCountryAndState($STaddressList[$ke]); 
				   if (empty($address->address_type_name))
				    {
					  $address->address_type_name = JText::_('COM_VIRTUEMART_USER_FORM_ADDRESS_LABEL'); 
					  //$address->address_type_name = JText::_('JACTION_EDIT'); 
					}
					
				 if (version_compare($x, '2.0.3', '>=')) 
				 {
				  $STaddressList[$ke]->edit_link = JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&cid[]='.$uid.'&virtuemart_userinfo_id='.$address->virtuemart_userinfo_id, true, $useSSL); 		
				 }
				 else
				 {
				   $STaddressList[$ke]->edit_link = JRoute::_('index.php?option=com_virtuemart&view=user&task=editAddressSt&addrtype=ST&cid[]='.$uid.'&virtuemart_userinfo_id='.$address->virtuemart_userinfo_id, true, $useSSL); 
				 }


				   }
				 if (version_compare($x, '2.0.3', '>=')) 
				 {
					
				  //206: index.php?option=com_virtuemart&view=user&task=editaddresscart&new=1&addrtype=ST&cid[]=51
				  $new_address_link = JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscart&new=1&addrtype=ST&cid[]='.$uid, true, $useSSL);
				  //vm200 'index.php?option=com_virtuemart&view=user&task=editAddressSt&new=1&addrtype=ST&cid[]='.$uid
				  //vm206: index.php?option=com_virtuemart&view=user&task=editaddresscart&new=1&addrtype=ST&cid[]=51
				 }
				 else
				 {
				   $new_address_link = JRoute::_('index.php?option=com_virtuemart&view=user&task=editAddressSt&new=1&addrtype=ST&cid[]='.$uid, true, $useSSL); 
				 }

				//version_compare(
				//vm204: index.php?option=com_virtuemart&view=user&task=editaddresscart&new=1&addrtype=ST&cid[]=51
				$vars = array(
				 'STaddress' => $STaddress, 
				 'bt_user_info_id' => $virtuemart_userinfo_id, 
				 'BTaddress' => $BTaddress,
				 'STaddressList' => $STaddressList,
				 'uid'=>$uid,
				 'cart'=>$ref->cart,
				 'new_address_link' => $new_address_link, 
				 
				);
				
				//$ref->cart->STaddress = $STaddress; 
				//$ref->cart->BTaddress = $BTaddress; 
				
				$html =  $this->fetch($this, 'list_shipto_addresses.tpl', $vars); 
				
				//$html = str_replace('id="'.$virtuemart_userinfo_id.'"', ' onclick="javascript:op_runSS(this);" id="id'.$virtuemart_userinfo_id.'" ', $html); 
				foreach ($STaddressList as $ST)
				 {
				   $html = str_replace('for="'.$ST->virtuemart_userinfo_id.'"', ' for="id'.$ST->virtuemart_userinfo_id.'" ', $html); 
				   $html = str_replace('id="'.$ST->virtuemart_userinfo_id.'"', ' id="id'.$ST->virtuemart_userinfo_id.'" onclick="javascript:op_runSS(this);" ', $html); 
				 }
				   $html = str_replace('for="'.$virtuemart_userinfo_id.'"', ' for="id'.$virtuemart_userinfo_id.'" ', $html); 
				   $html = str_replace('id="'.$virtuemart_userinfo_id.'"', ' id="id'.$virtuemart_userinfo_id.'" onclick="javascript:op_runSS(this);" ', $html); 

				return $html; 
			}
// variables outside the form, so it does not slow down the POST			
function getExtras(&$ref)
{
  return $this->getStateList($ref); 
}
  // we will not use json or jquery here as it is extremely unstable when having too many scripts on the site
  
  function getStateList(&$ref)
  {
    
    require_once(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'state.php'); 
	require_once(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'country.php'); 
	$countryModel = new VirtueMartModelCountry(); 
	$list = $countryModel->getCountries(true, true, false); 
	$countries = array();
    $states = array(); 	
	$stateModel = new VirtueMartModelState();
	$html = '<div style="display: none;"><form>'; 
	foreach ($list as $c)
	{
	  $states[$c->virtuemart_country_id] = $stateModel->getStates( $c->virtuemart_country_id, true );
	  unset($state); 
		//$html .= '<input type="hidden" name="opc_state_list" id="state_for_'.$c->virtuemart_country_id.'" value="" />'; 	  
	  if (!empty($states[$c->virtuemart_country_id])) 
	  {
	  $html .= '<select id="state_for_'.$c->virtuemart_country_id.'">'; 
	  foreach ($states[$c->virtuemart_country_id] as $state)
	   {
	     $html .= '<option value="'.$state->virtuemart_state_id.'">'.htmlentities($state->state_name).'</option>'; 
	   }
	  $html .= '</select>';
	  }
	  // debug

	  
	  
	}
	$html .= '</form></div>'; 
	return $html; 

  }
			
 function getMediaData($id)
 {
   if (empty($id)) return;
   if (is_array($id)) $id = reset($id);
   $db =& JFactory::getDBO(); 
   $q = "select * from #__virtuemart_medias where virtuemart_media_id = '".$db->getEscaped($id)."' "; 
   $db->setQuery($q); 
   $res = $db->loadAssoc(); 
   $err = $db->getErrorMsg(); 
   
   return $res; 
 }
 function getImageFile($id, $w=0, $h=0)
 {
   $img = $this->getMediaData($id);
   
   if (!empty($img['file_url_thumb']))
    {
	  $th = $img['file_url_thumb']; 
	  if (!empty($w) && (!empty($h)))
	  {
	  $th2 = str_replace('/resized/', '/resized_'.$w.'x'.$h, $th); 
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th2); 
	  if (file_exists($thf)) return $thf;
	  }
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th); 
	  if (file_exists($thf)) 
	  {
	  $tocreate = true; 
	  return $thf;
	  }
	}
   else
    {
	  $th = $img['file_url']; 
	  if (!empty($w) && (!empty($h)))
	  {
	  $th2 = str_replace('/virtuemart/', '/virtuemart/resized_'.$w.'x'.$h, $th); 
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th2); 
	  if (file_exists($thf)) return $thf;
	  }
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th); 
	  if (file_exists($thf)) 
	  	{
	    $tocreate = true; 
		return $thf;
		}
	}
 
 }
 function getImageUrl($id, &$tocreate, $w=0, $h=0)
 {
   $img = $this->getMediaData($id);
   if (!empty($img['file_url_thumb']))
    {
	  $th = $img['file_url_thumb']; 
	  $th2 = str_replace('/resized/', '/resized_'.$w.'x'.$h, $th); 
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th2); 
	  if (file_exists($thf)) return $th2;
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th); 
	  if (file_exists($thf)) 
	  {
	  $tocreate = true; 
	  return $th;
	  }
	}
   else
    {
	  $th = $img['file_url']; 
	  $th2 = str_replace('/virtuemart/', '/virtuemart/resized_'.$w.'x'.$h, $th); 
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th2); 
	  if (file_exists($thf)) return $th2;
	  $thf = JPATH_SITE.DS.str_replace('/', DS, $th); 
	  if (file_exists($thf)) 
	  	{
	    $tocreate = true; 
		return $th;
		}
	}
 }
 function getActionUrl(&$ref, $onlyindex=false)
 {
   if ($onlyindex) return JURI::base(true).'/index.php'; 
   return JURI::base(true).'/index.php?option=com_virtuemart&amp;view=opc&amp;controller=opc';
 }
 function &getBasket(&$ref)
 {
   include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
   
   $currencyDisplay = CurrencyDisplay::getInstance($ref->cart->pricesCurrency);
   
    $selected_template = 'icetheme_thestore'; 
    global $VM_LANG;
		  $product_rows = array(); 
		  $p2 = $ref->cart->products;
		  
		  if (empty($ref->cart))
		  {
		    $ref->cart = & VirtueMartCart::getCart();
		  }
		  $calc = calculationHelper::getInstance(); 
		  
		  $ref->cart->prices = $calc->getCheckoutPrices(  $ref->cart, false);
		  $useSSL = VmConfig::get('useSSL', 0);
		  	foreach( $ref->cart->products as $pkey =>$prow )
			{
			
			  $product = array();
			  $id = $prow->virtuemart_media_id;
			  if (empty($id)) $imgf = ''; 
			  else
			  {
			  if (is_array($id)) $id=reset($id); 
			  $imgf = $this->getImageFile($id); 
			  }
			  
			  $product['product_full_image'] = $imgf;
			
			  //$product['product_name'] =& $prow->product_name; 
			  $product['product_name'] = JHTML::link($prow->url, $prow->product_name, ' class="opc_product_name" ' );
			  $product['product_attributes'] = $prow->customfields;
			  $product['product_sku'] =  $prow->product_sku;
			  if (isset($prow->prices))
			  $product['product_price'] = $prow->prices['salesPrice'];
			  else
			  $product['product_price'] = $prow->salesPrice;
			  
			  $price_raw = $product['product_price']; 
			  
			  // the quantity is not working up to 2.0.4
			  $product['product_price'] = $currencyDisplay->createPriceDiv('salesPrice','', $ref->cart->pricesUnformatted[$pkey],false,false, 1);
			  
			  $action_url = $this->getActionUrl($this, true); 
			  $product['update_form'] = '<form action="'.$action_url.'" method="post" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="text" title="'.JText::_('COM_VIRTUEMART_CART_UPDATE').'" class="inputbox" size="3" maxlength="4" name="quantity" value="'.$prow->quantity.'" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_virtuemart_product_id" value="'.$prow->cart_item_id.'" />
				<input type="submit" class="vmicon vm2-add_quantity_cart" name="update" title="'.JText::_('COM_VIRTUEMART_CART_UPDATE').'" align="middle" value=" "/>
			  </form>'; 
			  
			  $product['delete_form'] = '<a class="vmicon vm2-remove_from_cart" title="'.JText::_('COM_VIRTUEMART_CART_DELETE').'" align="middle" href="'.JRoute::_('index.php?option=com_virtuemart&view=cart&task=delete&cart_virtuemart_product_id='.$prow->cart_item_id, true, $useSSL  ).'"> </a>'; 
			  
			  if (isset($prow->prices))
			  {
			  $product['subtotal'] = $prow->quantity * $price_raw;
			   
			  }
			  else
			  $product['subtotal'] = $prow->subtotal_with_tax;
			  
			  
			  
			  // this is fixed from 2.0.4 and would not be needed
			  $copy = $ref->cart->pricesUnformatted[$pkey];
			  $copy['salesPrice'] = $copy['subtotal_with_tax']; 
			  
			  $product['subtotal'] = $currencyDisplay->createPriceDiv('salesPrice','', $copy,false,false, 1);
			  // opc vars
			  $shipping_inside_basket = false;
			  $shipping_select = false;
			  $payment_select = false;
			  
			  $product_rows[] = $product; 
			 
			}
			if (!empty($ref->cart->prices['salesPriceCoupon']))
			{
			 $coupon_display = $currencyDisplay->createPriceDiv('salesPriceCoupon','', $ref->cart->prices,false,false, 1);//$ref->cart->prices['salesPriceCoupon']; 
			}
			else $coupon_display = ''; 
			$subtotal_display = $currencyDisplay->createPriceDiv('salesPrice','', $ref->cart->prices,false,false, 1); //$ref->cart->prices['salesPrice'];
			$order_total_display = $currencyDisplay->createPriceDiv('billTotal','', $ref->cart->prices,false,false, 1); //$ref->cart->prices['billTotal']; 
			
			// this will need a little tuning
			foreach($ref->cart->cartData['taxRulesBill'] as $rule){ 
				$rulename = $rule['calc_name'];
				$ref->cart->prices[$rule['virtuemart_calc_id'].'Diff']; 
				$tax_display = $currencyDisplay->createPriceDiv($rule['virtuemart_calc_id'].'Diff','', $ref->cart->prices,false,false, 1); //$ref->cart->prices[$rule['virtuemart_calc_id'].'Diff'];  
	  	    }
			if (empty($tax_display)) $tax_display = ''; 
			$no_shipping = $op_disable_shipping;
			$vars = array ('product_rows' => $product_rows, 
						   'shipping_select' => $shipping_select, 
						   'payment_select' => $payment_select, 
						   'shipping_inside_basket' => $shipping_inside_basket, 
						   'coupon_display' => $coupon_display, 
						   'subtotal_display' => $subtotal_display, 
						   'no_shipping' => $no_shipping,
						   'order_total_display' => $order_total_display, 
						   'tax_display' => $tax_display, 
						   'VM_LANG' => $VM_LANG,
						   );
			$html = $this->fetch($this, 'basket.html', $vars); 
			return $html;
			if (!empty($selected_template))
			include_once(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'overrides'.DS.'basket.html.php'); 	 
			else
			include_once(JPATH_OPC.DS.'themes'.DS.'overrides'.DS.'basket.html.php'); 	 
			$basket_html = ob_get_clean(); 
			
			return $basket_html; 
 }
 
 function getPayment(&$ref)
 {
   include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
    	$payment_not_found_text='';
		$payments_payment_rates=array();
		if (!$this->checkPaymentMethodsConfigured()) {
			$this->assignRef('paymentplugins_payments', $payments_payment_rates);
			$this->assignRef('found_payment_method', $found_payment_method);
		}

		$selectedPayment = empty($ref->cart->virtuemart_paymentmethod_id) ? 0 : $ref->cart->virtuemart_paymentmethod_id;

		$paymentplugins_payments = array();
		if(!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS.DS.'vmpsplugin.php');
		JPluginHelper::importPlugin('vmpayment');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmDisplayListFEPayment', array($ref->cart, $selectedPayment, &$paymentplugins_payments));
		// if no payment defined
		$found_payment_method = false;
		foreach ($returnValues as $returnValue) {
			if($returnValue){
				$found_payment_method = true;
				break;
			}
		}

		if (!$found_payment_method) {
			$link=''; // todo
			$payment_not_found_text = JText::sprintf('COM_VIRTUEMART_CART_NO_PAYMENT_METHOD_PUBLIC', '<a href="'.$link.'">'.$link.'</a>');
		}

		//$this->assignRef('payment_not_found_text', $payment_not_found_text);
		//$this->assignRef('paymentplugins_payments', $paymentplugins_payments);
		//$this->assignRef('found_payment_method', $found_payment_method);
	$ret = array(); 
	if ($found_payment_method) {

		foreach ($paymentplugins_payments as $paymentplugin_payments) {
		    if (is_array($paymentplugin_payments)) {
			foreach ($paymentplugin_payments as $paymentplugin_payment) {
				$paymentplugin_payment = str_replace('name="virtuemart_paymentmethod_id"', 'name="virtuemart_paymentmethod_id" onclick="javascript: runPayCC(\'\',\'\',op_textinclship, op_currency, 0)" ', $paymentplugin_payment); 
			    $ret[] = $paymentplugin_payment;
			}
		    }
		}
    

    } else {
	 $ret[] = $payment_not_found_text;
    }
	
	   $vars = array('payments' => $ret, 
				 'cart', $ref->cart, );
	   $html = $this->fetch($this, 'list_payment_methods.tpl', $vars); 
 $pid = JRequest::getVar('payment_method_id', ''); 
 if (!empty($pid) && (is_numeric($pid)))
 {
 if (strpos($html, 'value="'.$pid.'"')!==false)
 {
 
	$html = str_replace('checked="checked"', '', $html); 
	$html = str_replace('checked', '', $html); 
	$html = str_replace('value="'.$pid.'"', 'value="'.$pid.'" checked="checked" ', $html); 
 }
 
 }
 else
 if (strpos($html, 'value="'.$payment_default.'"')!==false)
 {
	$html = str_replace('checked="checked"', '', $html); 
	$html = str_replace('checked', '', $html); 
	$html = str_replace('value="'.$payment_default.'"', 'value="'.$payment_default.'" checked="checked" ', $html); 
	
 }
	return '<div id="payment_html">'.$html.'</div>';
 }

 
 
 function getShipping(&$ref, &$cart, $ajax=false)
 {
	if (empty($cart))
	{
     if (!empty($ref->cart))
		{
		  $cart =& $ref->cart; 
		}
		else
		  $cart =& VirtueMartCart::getCart(false, false); 
	}
	
   if (!$ajax)
   {
    // so we don't update the address twice   
     require_once(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'controllers'.DS.'opc.php'); 
	 $c = new VirtueMartControllerOpc(); 
     $c->setAddress($cart, true); 
   }	
      require_once(JPATH_OPC.DS.'helpers'.DS.'ajaxhelper.php'); 

	  
	
   
   $bhelper = new basketHelper; 
   //var_dump($arr); 
   $sh = $bhelper->getShippingArrayHtml($ref, $cart, $ajax);

   
	
   $ph = $bhelper->getPaymentArray(); 
	
	
   
  
    $bhelper->createDefaultAddress($ref, $cart); 
	$html = $bhelper->getPaymentArrayHtml($ref->cart, $ph, $sh); 
	$bhelper->restoreDefaultAddress($ref, $cart); 
	//$ret = implode('<br />', $sh); 
	$ret = '';
	
	$ret .= $html; 
  
	
	return $ret; 
 }
 function setDefaultShipping($sh, $ret)
 {
 }
 function addListeners($html)
 {
   	//if (constant('NO_SHIPPING') != '1')
	{
	// add ajax to zip, address1, address2, state, country
	$html = str_replace('id="shipto_zip_field"', ' onblur="javascript:op_runSS(this);" id="shipto_zip_field"', $html);
	$html = str_replace('id="shipto_address_1_field"', ' id="shipto_address_1_field" onblur="javascript:op_runSS(this);" ', $html); 
	$html = str_replace('id="shipto_address_2_field"', ' id="shipto_address_2_field" onblur="javascript:op_runSS(this);" ', $html); 
	// original: $html = str_replace('class="inputbox" name="sa_etats"', 'class="inputbox" name="sa_etats" onchange="javascript:op_runSS(this);" ', $html);
	$html = str_replace('id="shipto_virtuemart_state_id"', 'id="shipto_virtuemart_state_id" onchange="javascript:op_runSS(this);" ', $html);
	
	$cccount = strpos($html, '"shipto_virtuemart_state_id"'); 

	 if ($cccount !== false)
	 {
	   $par = "'true', ";
	   $isThere = true;
	 }
	 else
	 {
	     $par = "'false', ";
	     $isThere = false;
	 }
	  $html = str_replace('id="shipto_virtuemart_country_id"', 'id="shipto_virtuemart_country_id" onchange="javascript: op_validateCountryOp2('.$par.'\'true\', this);" ', $html, $count);
	}
	
	 // state fields
	 $cccount = strpos($html, '"virtuemart_state_id"'); 
	 if ($cccount !== false)
	 {
	   $par = "'true', ";
	   $isThere = true;
	 }
	 else
	 {
	     $par = "'false', ";
	     $isThere = false;
	 }
	 
	 $count = 0; 
	$html = str_replace('id="zip_field"', ' onblur="javascript:op_runSS(this);" id="zip_field"', $html);
	$html = str_replace('id="address_1_field"', ' id="address_1_field" onblur="javascript:op_runSS(this);" ', $html); 
	$html = str_replace('id="address_2_field"', ' id="address_2_field" onblur="javascript:op_runSS(this);" ', $html); 
	// original: $html = str_replace('class="inputbox" name="sa_etats"', 'class="inputbox" name="sa_etats" onchange="javascript:op_runSS(this);" ', $html);
	$html = str_replace('id="virtuemart_state_id"', 'id="virtuemart_state_id" onchange="javascript:op_runSS(this);" ', $html);

	 $html = str_replace('id="virtuemart_country_id"', 'id="virtuemart_country_id" onchange="javascript: op_validateCountryOp2('.$par.'\'false\', this);" ', $html, $count);
	
	return $html;
 }
 function getJSValidatorScript($obj)
 {
   return $this->fetch($this, 'formvalidator', array()); 
 }
 
 function isRegistered()
 {
 }
 function getRegistrationHhtml($obj)
 {
  include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
    if (!class_exists('VirtueMartCart'))
	 require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	 
	if (!empty($obj->cart))
	$cart =& $obj->cart; 
	else
	$cart =& VirtueMartCart::getCart();
	
  
   
   
    $type = 'BT'; 
   $this->address_type = 'BT'; 
   // for unlogged
   $virtuemart_userinfo_id = 0;
   $this->$virtuemart_userinfo_id = 0;
   $new = 1; 
   $fieldtype = $type . 'address';
   $cart->prepareAddressDataInCart($type, $new);
   if (!class_exists('VirtuemartModelUser'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');

   $this->setRegType(); 		

   
   if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
   $corefields = VirtueMartModelUserfields::getCoreFields();
   $userFields = $cart->$fieldtype;

   $this->_model = new VirtuemartModelUser();
    $layout = 'default';
   //$registration_html;
   $fields['fields'] = array(); 
   foreach ($corefields as $f)
   foreach ($userFields['fields'] as $key=>$uf)   
   {
     if (!empty($op_usernameisemail) && ($uf['name'] == 'username')) continue; 
     if ($f == $uf['name'] && ($uf['name'] != 'agreed'))
	 {
	  $l = $userFields['fields'][$key];
	  if ($key != 'email')
	  {
	  $l['formcode'] = str_replace('/>', ' autocomplete="off" />', $l['formcode']); 
	  }
	  else
	  {
	   
	    $user = JFactory::getUser();
		// special case in j1.7 - guest login (activation pending)
	   	if (!empty($currentUser->guest) && ($currentUser->guest == '1'))
		{
		  // we have a guest login here, therefore we will not let the user to change his email
		  $l['formcode'] = str_replace('/>', ' readonly="readonly" />', $l['formcode']); 
		}
		else
		{
		$uid = $user->get('id');
		// user is logged, but does not have a VM account
		if ((!$this->logged($cart)) && (!empty($uid)))
		{
		  // the user is logged in only in joomla, but does not have an account with virtuemart
		  $l['formcode'] = str_replace('/>', ' readonly="readonly" />', $l['formcode']); 
		}
		}
	  }
	  if ($key == 'password')
	   {
	     $userFields['fields']['opc_password'] = $userFields['fields'][$key];
		 $userFields['fields']['opc_password']['formcode'] = str_replace('password', 'opc_password', $userFields['fields']['opc_password']['formcode']); 
		 $userFields['fields']['opc_password']['formcode'] = str_replace('type="opc_password"', 'type="password" autocomplete="off" ', $userFields['fields']['opc_password']['formcode']); 
		 $userFields['fields']['opc_password']['name'] = 'opc_password'; 
		 //unset($userFields['fields'][$key]); 
		 $l = $userFields['fields']['opc_password'];
		 
	   }
	  
	 $fields['fields'][]  = $l;
     }
   }
   
   
   $vars = array('rowFields' => $fields, 
				 'cart', $obj, );
   $html = $this->fetch($this, 'list_user_fields.tpl', $vars); 
   $html = str_replace("'password'", "'opc_password'", $html); 
   return $html; 
   
 }
 function setRegType()
 {
   if (!defined('VM_REGISTRATION_TYPE'))
   {
    if (VmConfig::get('oncheckout_only_registered', 0))
	{
	  if (VmConfig::get('oncheckout_show_register', 0))
	  define('VM_REGISTRATION_TYPE', 'NORMAL_REGISTRATION'); 
	  else 
	  define('VM_REGISTRATION_TYPE', 'SILENT_REGISTRATION'); 
	}
	else
	{
	if (VmConfig::get('oncheckout_show_register', 0))
    define('VM_REGISTRATION_TYPE', 'OPTIONAL_REGISTRATION'); 
	else 
	define('VM_REGISTRATION_TYPE', 'NO_REGISTRATION'); 
	}
   } 
 }
 function getSTfields(&$obj)
 {
  
  include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
  if ($this->logged($obj->cart))
  {
    return $this->getUserInfoST($obj); 
  }
 
    if (!class_exists('VirtueMartCart'))
	 require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	
	if (!empty($obj->cart))
	$cart =& $obj->cart; 
	else
	$cart =& VirtueMartCart::getCart();
		
   $type = 'ST'; 
   $this->address_type = 'ST'; 
   // for unlogged
   $virtuemart_userinfo_id = 0;
   $this->$virtuemart_userinfo_id = 0;
   $new = 1; 
   $fieldtype = $type . 'address';
   $cart->prepareAddressDataInCart($type, $new);
   if (!class_exists('VirtuemartModelUser'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
   
	$this->setRegType(); 
   if (!defined('NO_SHIPTO'))
   define('NO_SHIPTO', $op_disable_shipto);
   if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
   $corefields = VirtueMartModelUserfields::getCoreFields();
   $userFields = $cart->$fieldtype;
   
 
   //foreach ($corefields as $f)
   foreach ($userFields['fields'] as $key=>$uf)   
   {
     if (!empty($corefields))
     foreach($corefields as $k=>$f)
	  {

	    if ($f == $uf['name'])
		 {
	 	   unset($userFields['fields'][$key]);   
		   unset($corefields[$k]);
		 }
	  }
     
	 //if (false)
	 if (isset($userFields['fields'][$key]))
	 {
	 $userFields['fields'][$key]['required'] = 0;

	 if ($key == 'virtuemart_state_id')
	  {
	    
	    //$f2 = $userFields['fields'][$key]; 
		//unset($userFields['fields'][$key]); 
		$userFields['fields']['virtuemart_state_id']['formcode'] = str_replace('id="virtuemart_state_id"', 'id="'.$userFields['fields']['virtuemart_state_id']['name'].'"', $userFields['fields']['virtuemart_state_id']['formcode']); 
	  }
	 //$orig = $userFields['fields'][$key]['name'];
	 //$new = 'sa_'.strrev($orig); 
	 //$userFields['fields'][$key]['name'] = $new;
	 //$userFields['fields'][$key]['formcode'] = $this->reverseId($userFields['fields'][$key]['formcode'], $orig, $new ); 
	 }
   }
   $this->_model = new VirtuemartModelUser();
   $layout = 'default';
  
 
   $vars = array('rowFields' => $userFields, 
				 'cart', $obj, );
   $html = $this->fetch($this, 'list_user_fields_shipping.tpl', $vars); 
   $html = $this->addListeners($html);
   $html = str_replace('class="required"', 'class="opcrequired"', $html);
   return $html;
   
 }
 function reverseId($html, $orig, $new)
 {
   // replaces name and id
   $html = str_replace($orig, $new, $html); 
   //$html = str_replace('id="'.$orig.'_field', 'id="'.$new.'_field', $html); 
   return $html;
 }
 function logged(&$cart)
 {
   if (!class_exists('VirtuemartModelUser'))
				require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
				$umodel = new VirtuemartModelUser();
				
				$virtuemart_userinfo_id = 0; 
				$currentUser =& JFactory::getUser();
				$uid = $currentUser->get('id');
				// support for j1.7+
				if (!empty($currentUser->guest) && ($currentUser->guest == '1')) return false; 
				
				if (empty($uid)) return false; 
				
				$db =& JFactory::getDBO(); 
				$q = "select virtuemart_userinfo_id from #__virtuemart_userinfos where virtuemart_user_id = '".$uid."' and address_type = 'BT' limit 0,1 "; 
				$db->setQuery($q); 
				$uid = $db->loadResult(); 
				if (empty($uid)) return false;
				
				$userFields = $umodel->getUserInfoInUserFields('default', 'BT', $uid);
				
				
				
				if (empty($userFields[$virtuemart_userinfo_id]))
				$virtuemart_userinfo_id = $umodel->getBTuserinfo_id();
				else $virtuemart_userinfo_id = $userFields[$virtuemart_userinfo_id]; 
				
				$id = $umodel->getId(); 
				
				if (empty($virtuemart_userinfo_id)) return false; 
				else return true;
 
 
  if (!class_exists('VirtueMartModelUser'))
  require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
  $usermodel = new VirtueMartModelUser();
  $user = JFactory::getUser();
  $usermodel->setId($user->get('id'));

  
  $user = $usermodel->getUser();
  
  if (empty($user->virtuemart_user_id)) return false;
  if (!empty($cart) && (!empty($cart->BTaddress))) return true; 
   return false; 
 }
 function getVendorInfo(&$cart)
 {
  if (empty($cart->vendorId)) $vendorid = 1; 
  else $vendorid = $cart->vendorId;

  $dbj =& JFactory::getDBO(); 

  $q = "SELECT * FROM `#__virtuemart_userinfos` as ui, #__virtuemart_vmusers as uu WHERE ui.virtuemart_user_id = uu.virtuemart_user_id and uu.virtuemart_vendor_id = '".(int)$vendorid."' limit 0,1";
  $dbj->setQuery($q);
	
   $vendorinfo = $dbj->loadAssoc();

	return $vendorinfo; 

 }
 function getBTfields(&$obj)
 {
   include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
   // $default_shipping_country
   if ($this->logged($obj->cart))
   {
     return $this->getUserInfoBT($obj); 
   }
   else
   {
    if (!class_exists('VirtueMartCart'))
	 require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
	
	if (!empty($obj->cart)) 
	$cart =& $obj->cart; 
	else
	$cart =& VirtueMartCart::getCart();
		
   $type = 'BT'; 
   $this->address_type = 'BT'; 
   // for unlogged
   $virtuemart_userinfo_id = 0;
   $this->$virtuemart_userinfo_id = 0;
   $new = 1; 
   $fieldtype = $type . 'address';
   
   if (empty($cart->BT)) $cart->BT = array(); 
   if (empty($cart->BT['virtuemart_country_id'])) 
   {
    if (!empty($default_shipping_country) && (is_numeric($default_shipping_country)))
	 {
	   $cart->BT['virtuemart_country_id'] = $default_shipping_country; 
	 }
	 else
	 {
    // let's set a default country
	$vendor = $this->getVendorInfo($cart); 
	$cart->BT['virtuemart_country_id'] = $vendor['virtuemart_country_id']; 
	 }
   }
 
   $cart->prepareAddressDataInCart($type, false);
   if (!class_exists('VirtuemartModelUser'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'user.php');
   
   $this->setRegType(); 
   if (!defined('NO_SHIPTO'))
   define('NO_SHIPTO', $op_disable_shipto);
   if(!class_exists('VirtuemartModelUserfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'userfields.php');
   $corefields = VirtueMartModelUserfields::getCoreFields();
   $userFields = $cart->$fieldtype;
  
   foreach ($corefields as $f)
   foreach ($userFields['fields'] as $key=>$uf)   
   {
     if ($f == $uf['name'])
	 unset($userFields['fields'][$key]);
	 else
	 if (!empty($uf['required']) && (strpos($uf['formcode'], 'required')===false))
	 if ($userFields['fields'][$key]['name'] != 'virtuemart_state_id')
	  {
	    $x1 = strpos($uf['formcode'], 'class="');
		if ($x1 !==false)
		{
		  $userFields['fields'][$key]['formcode'] = str_replace('class="', 'class="required ', $uf['formcode']);
		}
	  }
   }
   $this->_model = new VirtuemartModelUser();
    $layout = 'default';
  
  
   $vars = array('rowFields' => $userFields, 
				 'cart', $obj, );
   $html = $this->fetch($this, 'list_user_fields.tpl', $vars); 
   $html = $this->addListeners($html);
   
   return $html;
   }
 }
 
 function getJavascript($ref)
 {
   
   include (JPATH_OPC.DS.'ext'.DS.'extension.php');
   require_once(JPATH_OPC.DS.'helpers'.DS.'ajaxhelper.php'); 
   
   $bhelper = new basketHelper; 

   $extHelper = new opExtension();
   $extHelper->runExt('before');

   include(JPATH_OPC.DS.'config'.DS.'onepage.cfg.php'); 
   
  // $ccjs = "\n".' var op_general_error = "'.$this->slash(JText->_('CONTACT_FORM_NC')).'"; '."\n";
  // $ccjs .= ' var op_cca = "~';
   // COM_VIRTUEMART_ORDER_PRINT_PAYMENT
   
   	$extJs = " var shipconf = []; var payconf = []; "."\n";
	$extJs .= ' var op_shipping_div = null; ';
	$extJs .= ' var op_lastq = ""; ';
	if (!empty($op_loader))
	{
	  $extJs .= ' var op_loader = true; 
	  			var op_loader_img = "'.JURI::root().'media/system/images/mootree_loader.gif";';
	}	
	else $extJs .= ' var op_loader = false; ';
	
	
	
	if (!empty($onlyd))
	$extJs .= ' var op_onlydownloadable = "1"; ';
	else $extJs .= ' var op_onlydownloadable = ""; ';

		
	if (!empty($op_last_field))
	$extJs .= ' var op_last_field = true; ';
	else $extJs .= ' var op_last_field = false; ';
	
	$extJs .= ' var op_refresh_html = ""; ';

	// stAn mod for OPC2
	/*
	if (!empty($op_delay_ship))
	$extJs .= " var op_delay = true; ";
	else $extJs .= " var op_delay = false; ";
	*/


	if (!empty($op_delay_ship))
	$extJs .= " var op_delay = false; ";
	else $extJs .= " var op_delay = false; ";

	
	if (empty($last_ship2_field)) $last_ship2_field = ''; 
	if (empty($last_ship_field)) $last_ship_field = ''; 
	
	$extJs .= " var op_last1 = '".$this->slash($last_ship_field)."'; ";
	$extJs .= " var op_last2 = '".$this->slash($last_ship2_field)."'; ";

	
	$url = JURI::base(true); 
	if (empty($url)) $url = '/'; 
	if (substr($url, strlen($url)-1)!=='/') $url .= '/'; 
	$actionurl = $url.'index.php'; 
 if(version_compare(JVERSION,'2.5.0','ge')) {
	$extJs .= " var op_com_user = 'com_users'; "; 
	$extJs .= " var op_com_user_task = 'user.login'; "; 
	$extJs .= " var op_com_user_action = '".$actionurl."?option=com_users&task=user.login&controller=user'; "; 
  
 }
 else
 if(version_compare(JVERSION,'1.7.0','ge')) {
	$extJs .= " var op_com_user = 'com_users'; "; 
	$extJs .= " var op_com_user_task = 'user.login'; "; 
	$extJs .= " var op_com_user_action = '".$actionurl."?option=com_users&task=user.login&controller=user'; "; 

 // Joomla! 1.7 code here
} elseif(version_compare(JVERSION,'1.6.0','ge')) {
// Joomla! 1.6 code here
} else {	
	$extJs .= " var op_com_user = 'com_user'; "; 
	$extJs .= " var op_com_user_task = 'login'; "; 
	$extJs .= " var op_com_user_action = '".$actionurl."?option=com_user&task=login'; "; 
	}
	
	$op_autosubmit = false;
	$extHelper->runExt('autosubmit', '', '', $op_autosubmit);
	
	$extJs .= ' var callSubmitFunct = new Array(); ';
	$extJs .= ' var callAfterPaymentSelect = new Array(); '; 
	$extJs .= ' var callAfterShippingSelect = new Array(); '; 
	$extJs .= ' var callBeforePaymentSelect = new Array(); '; 
	$extJs .= ' var callBeforeAjax = new Array(); '; 
	$extJs .= ' var callAfterAjax = new Array(); ';
	$extJs .= ' var op_firstrun = true; ';
	$extHelper->runExt('addjavascript', '', '', $extJs);
	$extJs .= $extJs;
	
	if (empty($op_autosubmit))
	$extJs .= " var op_autosubmit = false; ";
	else 
	{ 
	 $extJs .= " var op_autosubmit = true; ";
	
	}
	$db=&JFactory::getDBO();
	$q = 'select * from #__virtuemart_vendors where virtuemart_vendor_id = 1 limit 0,1 '; 
	$db->setQuery($q); 
	$res = $db->loadAssoc(); 
	if (!empty($res)) extract($res); 
	
	//VmConfig::get('useSSL',0)
	
	$c = CurrencyDisplay::getInstance($ref->cart->paymentCurrency);
	// op_vendor_style = '1|&euro;|2|.|\'|3|0'; 
	$arr = array(); 
	$arr[0] = '1'; 
	$arr[1] = $c->getSymbol(); 
	$arr[2] = $c->getNbrDecimals(); 
	$arr[3] = $c->getDecimalSymbol(); 
	$arr[4] = $c->getThousandsSeperator(); 
	// for now
	$arr[5] = '3';
	$arr[6] = '8';
	$arr[7] = '8';
	$arr[8] = $c->getPositiveFormat(); 
	$arr[9] = $c->getNegativeFormat(); 
	$vendor_currency_display_style = implode('|', $arr);
	//$arr[2] = $c->
	$extJs .= " var op_saved_shipping = null; ";
	
	$cs = str_replace("'", '\\\'', $vendor_currency_display_style);
	$extJs .= " var op_vendor_style = '".$cs."'; ";
	//if (!empty($override_basket) || (!empty($shipping_inside_basket)) || (!empty($payment_inside_basket)))
	{
	 $extJs .= ' op_override_basket = true; ';
	 $extJs .= ' op_basket_override = true; ';
	}
	/*
	else 
	{
	 $extJs .= ' op_override_basket = false; ';
	 $extJs .= ' op_basket_override = false; ';
	}
	*/
        // google adwrods tracking code here
        if (!empty($adwords_enabled[0]) && (!empty($adwords_code[0])))
            {
             $extJs .= " var acode = '1'; ";
            }
            else
            {
              $extJs .= " var acode = '0'; ";
            }
	/*
	if (!empty($use_ssl))
	$extJs .= " var op_securl = '".SECUREURL."index.php'; ";
	else
	*/
	
	$ur = JURI::base(true); 
	if (substr($ur, strlen($ur)-1)!= '/')
	 $ur .= '/';
	//$ur .= basename($_SERVER['PHP_SELF']);
	$mm_action_url = $ur;
	$extJs .= " var op_securl = '".$ur."index.php'; ";
	$extJs .= " var pay_btn = new Array(); "; 
	$extJs .= " var pay_msg = new Array(); "; 
	$extJs .= " pay_msg['default'] = ''; ";
	
    $extJs .= " pay_btn['default'] = '".$this->slash(JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU'))."'; ";

        $extJs .= " var op_timeout = 0; ";
        $extJs .= " var op_maxtimeout = 3000; ";
        $extJs .= " var op_semafor = false; ";
	if (!empty($op_sum_tax))
	{
	    $extJs .= " var op_sum_tax = true; ";
	}
	else
	{
	  $extJs .= " var op_sum_tax = false; ";
	}
	if (defined("_MIN_POV_REACHED") && (constant("_MIN_POV_REACHED")=='1'))
	{
	 $extJs .= " var op_min_pov_reached = true; ";
	}
	else
	{
	 $extJs .= " var op_min_pov_reached = false; ";
	}
	
	$extJs .= " var payment_discount_before = '0'; ";
	if (empty($hidep) || (!empty($payment_inside)))
	{
	$extJs .= " var op_payment_disabling_disabled = true; ";
	}
	else
	{
	$extJs .= " var op_payment_disabling_disabled = false; ";
	}
	//$extJs .= " var op_show_prices_including_tax = '".$auth["show_price_including_tax"]."'; ";
	$extJs .= " var op_show_prices_including_tax = '1'; ";
	$extJs .= " var never_show_total = ";
	if ((isset($never_show_total) && ($never_show_total==true))) $extJs .= ' true; '."\n";
	else $extJs .= ' false; '."\n";
	$extJs .= " var op_no_jscheck = ";
	// modified for OPC2
	if (!empty($no_jscheck)) $extJs .= " true; "; else $extJs .= " true; ";
	$extJs .= " var op_no_taxes_show = ";
	if ((isset($no_taxes_show) && ($no_taxes_show==true))) $extJs .= ' true; '."\n";
	else $extJs .= ' false; '."\n";

	$extJs .= " var op_no_taxes = ";
	if ((isset($no_taxes) && ($no_taxes==true))) $extJs .= ' true; '."\n";
	else $extJs .= ' false; '."\n";
	
	$selectl = JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION');
	$extJs .= " var op_lang_select = '(".$selectl.")'; ";
	//if ((ps_checkout::tax_based_on_vendor_address()) && ($auth['show_price_including_tax']) && ((!isset($always_show_tax) || ($always_show_tax !== true))))
	//$extJs .= " var op_dont_show_taxes = '1'; ";
	//else
	$extJs .= " var op_dont_show_taxes = '0'; "."\n";
	$extJs .= ' var op_coupon_amount = "0"; '."\n";
	
	$extJs .= ' var op_shipping_txt = "'.$bhelper->utf8tohtml(JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_PRICE_LBL')).'"; '."\n"; 
	$extJs .= ' var op_shipping_tax_txt = "'.$bhelper->utf8tohtml(JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_TAX')).'"; '."\n"; 
  $country_ship = array();

    //if (constant("NO_SHIPPING")!="1")
	if (false)
	if (isset($hidep))
	foreach ($hidep as &$h)
	{ 
	  $h .= ','.$payments_to_hide.',';
	  $h = str_replace(' ', '', $h);
	  $h = ','.$h.',';
	}
	
	// found shipping methods
	// $sarr = $bhelper->getShippingArray();
	if (false)
	foreach ($sarr as $k=>$ship)
	{
	   if (isset($hidep[$ship->virtuemart_shipmentmethod_id]))
	   $extJs .= " payconf['".$k."']=\",".$hidep[$k].",\"; ";
	   else $extJs .= " payconf['".$k."']=\",\"; "; 
	  
	}
	// old code for standard shipping
	
	if (!empty($rows))
	foreach ($rows as $r)
	{
	 $id = $r['shipping_rate_id'];
	 $cs = $r['shipping_rate_country'];
	 $car = $r['shipping_rate_carrier_id'];
	 $k = explode(';', $cs, 1000);
	 foreach($k as $kk)
	 {
	  if ($kk!='')
	  {
	  $krajiny[] = $kk;
	  if (!isset($country_ship[$id]))
	    $country_ship[$id] = array();
	  $country_ship[$id][$kk] = $kk;
	  }
	 }
	 $extJs .= "shipconf[".$id."]=\"".$cs.'"; ';
	 
	}
		// end of old code for standard shipping
		
        
        // country_ship description:
        // country_ship[ship_id][country] = country
        // country_ship will be used for default shipping method for selected default shipping country
        
        // global variables: ordertotal, currency symbol, text for order total
//        echo $incship;
        $incship = JText::_('COM_ONEPAGE_ORDER_TOTAL_INCL_SHIPPING'); 	
        if (empty($incship)) $incship = JText::_('COM_VIRTUEMART_ORDER_LIST_TOTAL'); 		
        $incship = $this->slash($incship);

	if (!empty($order_total))
        $extJs .= " var op_ordertotal = ".$order_total."; ";
         else $extJs .= " var op_ordertotal = 0.0; ";
        $extJs .= " var op_textinclship = '".$this->slash(JText::_('COM_VIRTUEMART_CART_TOTAL'))."'; ";
        $extJs .= " var op_currency = '".$this->slash($c->getSymbol())."'; ";
        if (!empty($weight_total))
        $extJs .= " var op_weight = ".$weight_total."; ";
        else $extJs .= " var op_weight = 0.00; ";
        if (!empty($vars['zone_qty']))
        $extJs .= " var op_zone_qty = ".$vars['zone_qty']."; ";
        else $extJs .= " var op_zone_qty = 0.00; ";
        if (!empty($grandSubtotal))
        $extJs .= " var op_grand_subtotal = ".$grandSubtotal."; ";
        else $extJs .= " var op_grand_subtotal = 0.00; ";
        $extJs .= ' var op_subtotal_txt = "'.$this->slash(JText::_('COM_VIRTUEMART_CART_SUBTOTAL'), false).'"; ';
        $extJs .= ' var op_tax_txt = "'.$this->slash(JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL_TAX'), false).'"; ';
       
        if (!empty($op_disable_shipping))
        $nos = 'true'; 
		else 
		$nos = 'false';
		
        $extJs .= "var op_noshipping = ".$nos."; ";
		$extJs .= "var op_autosubmit = false; "; 
//        $extJs .= " var op_tok = '".$_SESSION['__default']['session.token']."'; ";
	// array of avaiable country codes
	if (!empty($krajiny))
	$krajiny = array_unique($krajiny);
   
	//echo 'krajiny: '.var_export($krajiny, true);
	//$arr = $ajaxpayment->getPaymentHtml(); 
	//$rp_js = $arr['js']; 
	$rp_js = ''; 
	$extJs .= $rp_js."\n";
	$ship_country_change_msg = JText::_('COM_ONEPAGE_SHIP_COUNTRY_CHANGED'); 
	$extJs .= ' var shipChangeCountry = "'.$bhelper->utf8tohtml($ship_country_change_msg).'"; '."\n";
	$ship_country_is_invalid_msg = JText::_('COM_ONEPAGE_SHIP_COUNTRY_INVALID'); 
	$extJs .= ' var noshiptocmsg = "'.$bhelper->utf8tohtml($ship_country_is_invalid_msg).'"; '."\n";
	$extJs .= " var default_ship = null; "."\n";
    $extJs .= ' var agreedmsg = "'.$bhelper->utf8tohtml(JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_NO', false)).'"; '."\n";
	$extJs .= ' var op_continue_link = ""; '."\n";
	if ($must_have_valid_vat)
        $extJs .= "var op_vat_ok = 2; var vat_input_id = \"".$vat_input_id."\"; var vat_must_be_valid = true; "."\n";
		$default_info_message = JText::_('COM_ONEPAGE_PAYMENT_EXTRA_DEFAULT_INFO'); 
        $extJs .= ' var payment_default_msg = "'.str_replace('"', '\"', $default_info_message).'"; '."\n";
        $extJs .= ' var payment_button_def = "'.str_replace('"', '\"', JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU')).'"; '."\n";
	if (empty($op_dontloadajax))
	$extJs .= ' var op_dontloadajax = false; ';
	else
	$extJs .= ' var op_dontloadajax = true; ';
        
	// adds payment discount array
	//if (isset($pscript))
	//$extJs .= $pscript;
	if (isset($payments_to_hide))
	{
	 $payments_to_hide = str_replace(' ', '', $payments_to_hide);
	}
	else
	 $payments_to_hide = "";

	// adds script to change text on the button
	if (isset($rp))
	$extJs .= $rp;
	if (!((isset($vendor_name)) && ($vendor_name!='')))
	$vendor_name = 'E-shop';
	$extJs .= ' var op_vendor_name = "'.$bhelper->utf8tohtml($vendor_name).'"; '."\n";
	if (!isset($_SESSION['__default']['session.token']))
	$_SESSION['__default']['session.token'] = md5(uniqid());
	$next_order_id = $bhelper->getNextOrderId(); 
	$g_order_id = $next_order_id."_".md5($_SESSION['__default']['session.token']);
	$extJs .= ' var g_order_id = "'.$g_order_id.'"; '."\n";
	$extJs .= ' var op_order_total = 0; '."\n";
	$extJs .= ' var op_total_total = 0; '."\n";
	$extJs .= ' var op_ship_total = 0; '."\n";
	$extJs .= ' var op_tax_total = 0; '."\n";
	if (empty($op_fix_ins))
	$extJs .= 'var op_fix_payment_vat = false; ';
	
	$extJs .= ' var op_run_google = new Boolean(';
	if ($g_analytics === true) 
	 $extJs .= 'true); ';
	else
	 $extJs .= 'false); ';
	if (!isset($pth_js)) 
	$pth_js = '';
    $extJs .= ' var op_always_show_tax = ';
    if (isset($always_show_tax) && ($always_show_tax===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";
    
    $extJs .= ' var op_always_show_all = ';
    if (isset($always_show_all) && ($always_show_all===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";
     
    $extJs .= ' var op_add_tax = ';
    if (isset($add_tax) && ($add_tax===true))
      $extJs .= 'true; ';
     else $extJs .= 'false; ';
    
    $extJs .= ' var op_add_tax_to_shipping = ';
    if (isset($add_tax_to_shipping) && ($add_tax_to_shipping===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";

    $extJs .= ' var op_add_tax_to_shipping_problem = ';
    if (isset($add_tax_to_shipping_problem) && ($add_tax_to_shipping_problem===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";


    $extJs .= ' var op_no_decimals = ';
    if (isset($no_decimals) && ($no_decimals===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";

    $extJs .= ' var op_curr_after = ';
    if (isset($curr_after) && ($curr_after===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";
	
	if (empty($op_basket_subtotal_taxonly)) $op_basket_subtotal_taxonly = '0.00';
	$extJs .= ' var op_basket_subtotal_items_tax_only = '.$op_basket_subtotal_taxonly.'; ';
/*
	can be send to js if needed: 
			$op_basket_subtotal += $price["product_price"] * $cart[$i]["quantity"];
		$op_basket_subtotal_withtax += ($price["product_price"] * $cart[$i]["quantity"])*($my_taxrate+1);
		$op_basket_subtotal_taxonly +=  ($price["product_price"] * $cart[$i]["quantity"])*($my_taxrate);
*/

	$extJs .= ' var op_show_only_total = ';
    if (isset($show_only_total) && ($show_only_total===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";
     
    $extJs .= ' var op_show_andrea_view = ';
    if (isset($show_andrea_view) && ($show_andrea_view===true))
      $extJs .= 'true; '."\n";
     else $extJs .= 'false; '."\n";
      
	$extJs .= ' var op_detected_tax_rate = "0"; ';
    $extJs .= ' var op_custom_tax_rate = ';
    if (empty($custom_tax_rate)) $custom_tax_rate = '0.00';
    $custom_tax_rate = str_replace(',', '.', $custom_tax_rate);
    $custom_tax_rate = str_replace(' ', '', $custom_tax_rate);
    if (!empty($custom_tax_rate) && is_numeric($custom_tax_rate))
      $extJs .= '"'.$custom_tax_rate.'"; '."\n";
     else $extJs .= '""; '."\n";

    $extJs .= ' var op_coupon_discount_txt = "'.$bhelper->utf8tohtml(JText::_('COM_VIRTUEMART_COUPON_DISCOUNT')).'"; '."\n";
    
    if (!empty($shipping_inside_basket))
    {
     $extJs .= " var op_shipping_inside_basket = true; ";
    }
    else $extJs .= " var op_shipping_inside_basket = false; ";

    if (!empty($payment_inside_basket))
    {
     $extJs .= " var op_payment_inside_basket = true; ";
    }
    else $extJs .= " var op_payment_inside_basket = false; ";
    
    
	$extJs .= " var op_disabled_payments = \"$pth_js\"; \n";
  
  	$extJs .= "var op_payment_discount = 0; \n var op_ship_cost = 0; \n var pdisc = []; "."\n";
    $extJs .= 'var op_payment_fee_txt = "'.str_replace('"', '\"', JText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT')).'"; '."\n"; // fee
    $extJs .= 'var op_payment_discount_txt = "'.str_replace('"', '\"', JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT')).'"; '."\n"; // discount
    //$rp_js = ' var pay_msg = []; var pay_btn = []; ';	
    
    // paypal:
    if (false && $paypalActive)
    $extJs .= ' var op_paypal_id = "'.ps_paypal_api::getPaymentMethodId().'"; ';
    else $extJs .= ' var op_paypal_id = "x"; ';
    if (false && $paypalActive && (defined('PAYPAL_API_DIRECT_PAYMENT_ON')) && ((boolean)PAYPAL_API_DIRECT_PAYMENT_ON))
    {
      $extJs .= ' var op_paypal_direct = true; ';
    }
    else
    {
      $extJs .= ' var op_paypal_direct = false; ';
    }
	
	$extJs .= ' var op_general_error = '."'".$this->slash(JText::_('COM_VIRTUEMART_USER_FORM_MISSING_REQUIRED_JS'))."';";
    $err = $this->getPwdError(); 
	
	$extJs .= ' var op_pwderror = '."'".$this->slash($err)."';\n";
   
	
    return $extJs; 
 }
 
 function slash($string, $insingle = true)
 {
 
   if ($insingle)
    {
	 $string = addslashes($string); 
     $string = str_replace('/"', '"', $string); 
	 return $string; 
	}
	else
	{
	  $string = addslashes($string); 
	  $string = str_replace("/'", "'", $string); 
	  return $string; 
	}
 }
 
 function getPwdError()
 {
   $jlang =& JFactory::getLanguage(); 
   	 if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) {

   $jlang->load('com_users', JPATH_SITE, 'en-GB', true); 
   $jlang->load('com_users', JPATH_SITE, $jlang->getDefault(), true); 
   $jlang->load('com_users', JPATH_SITE, null, true); 
   
   return JText::_('COM_USERS_FIELD_RESET_PASSWORD1_MESSAGE'); 
   
   }
   else
   {
    $jlang->load('com_user', JPATH_SITE, 'en-GB', true); 
    $jlang->load('com_user', JPATH_SITE, $jlang->getDefault(), true); 
    $jlang->load('com_user', JPATH_SITE, null, true); 

    return JText::_('PASSWORDS_DO_NOT_MATCH'); 
   }
 }
 
 function fetch(&$ref, $template, $vars, $new='')
 {
   include(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php');   
   $no_shipping = $op_disable_shipping;
  
   
   if (file_exists(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'overrides'.DS.$template.'.php'))
    {
	 
	  ob_start(); 
	  extract($vars); 
	  include(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'overrides'.DS.$template.'.php'); 
	  $ret = ob_get_clean(); 
	  
	  return $ret; 
	}
   else
    {
	  if (!empty($new))
	   {
	     $ly = $ref->layoutName; 
	     if (file_exists(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'views'.DS.'cart'.DS.'tmpl'.DS.$ly.'_'.$new.'.php'))
		  {
		    ob_start(); 
			include(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'views'.DS.'cart'.DS.'tmpl'.DS.$ly.'_'.$new.'.php'); 
			$ret = ob_get_clean(); 
			return $ret; 
		  }
	     
	   }
	}
 }
 function getCoupon(&$obj)
 {
   if (!VmConfig::get('coupons_enable')) 
   {
    return ""; 
   }
   $this->couponCode = (isset($this->cart->couponCode) ? $this->cart->couponCode : '');
   $coupon_text = $obj->cart->couponCode ? JText::_('COM_VIRTUEMART_COUPON_CODE_CHANGE') : JText::_('COM_VIRTUEMART_COUPON_CODE_ENTER');
   $this->assignRef('coupon_text', $coupon_text);
   return $this->fetch($obj, 'couponField.tpl', array(), 'coupon'); 
   
 }
 
 
 function renderOPC()
  {
    $selected_template = 'icetheme_thestore'; 
  }
   	function op_image_info_array($image, $args="", $resize=1, $path_appendix='product', $thumb_width=0, $thumb_height=0)
	{
	 
	 return $this->op_image_tag($image, $args, $resize, $path_appendix, $thumb_width, $thumb_height, true );
	}
    
	public function getJSValidator($ref)
	{
	  $html = 'javascript:return validateFormOnePage(true);" autocomplete="off'; 
	  //$html = '" autocomplete="off"'; 
	  return $html;
	}
function path2url($path)
	{
		$path = str_replace(JPATH_SITE, '', $path); 
		$path = str_replace(DS, '/', $path); 
		
		if (substr($path, 0, 1) != '/') $path = '/'.$path; 
		
		$base = JURI::base(true);
		
		
		if (substr($base, -1)=='/') $base = substr($base, 0, -1);

		$path = $base.$path; 
		
		return $path; 
	}
	function op_image_tag($image, $args="", $resize=1, $path_appendix='product', $thumb_width=0, $thumb_height=0, $retA = false ) {
    
	
	if (empty($image)) 
	 {
		  $image = VmConfig::get('vm_themeurl', JURI::root().'components/com_virtuemart/').'assets/images/vmgeneral/'.VmConfig::get('no_image_set'); 
	 }
	if (strpos($image, 'http')===0)
	{
	     // if the image starts with http
	     $imga = array();
		 $imga['width'] = $thumb_width;
		 $imga['height'] = $thumb_height;
		 $imga['iurl'] = $image;
	}
	
	
		$height = $width = 0;
		
		$ow = $thumb_width; 
		$oh = $thumb_height; 
		
		if ($image != "") {
			$fi = pathinfo($image);
			
			// to resize we need to know if to keep height or width
			$arr = getimagesize( $image );
			$width = $arr[0]; $height = $arr[1];
			
			if (empty($thumb_width) && (!empty($thumb_height)))
			{
			  $rate = $height / $thumb_height; // 1.5
			  $thumb_width = round($width / $rate);
			  // if width<height do nothing
			  //if ($width>$height && ())
			}
			else
			if (empty($thumb_height))
			{
			 $rate = $width / $thumb_width; 
			 $thumb_height = round($height / $rate); 
			}
			else
			if (empty($thumb_height) && (empty($thumb_width)))
			{
			  $thumb_height = $height;
			  $thumb_width = $width;
			}
			
			
			if (!empty($fi['extension']))
			{
			$basename = str_replace('.'.$fi['extension'], '', $fi['basename']); 
			$u = VmConfig::get('media_product_path'); 
			$u = str_replace('/', DS, $u); 
			
			$filename = JPATH_SITE.DS.$u.$ow.'x'.$oh.DS.$fi['basename']; 
			
			
			
			if (file_exists($filename)) 
			 { 
			   $arr = getimagesize( $filename );
			   $width = $arr[0]; $height = $arr[1];
			 }
			if (($width > $ow) || ($height > $oh) || (!(file_exists($filename))))
			 {
			 
			   if (!file_exists(dirname($filename))) 
			    {
				 				  jimport( 'joomla.filesystem.folder' );
				  jimport( 'joomla.filesystem.file' );
				  
				  @JFolder::create(dirname($filename)); 
				  @JFile::write(dirname($filename).DS.'index.html', ' '); 

				}
				$this->resizeImg($image, $filename, $ow, $oh, $width, $height); 
			    $arr = getimagesize( $filename );
			    $width = $arr[0]; $height = $arr[1];
				
			   // we need to create it
			   // should be here:
			   //
			   
			 }
			}

			

		}
		
		if ($retA===true)
		{
		 $imga = array();
		 $imga['width'] = $width;
		 $imga['height'] = $height;
		 $imga['iurl'] = $this->path2url($filename);
		 
		 
		 return $imga;
		}
		else 
		return '<img src="'.$url.'" />'; 
		//return vmCommonHTML::imageTag( $url, '', '', $height, $width, '', '', $args.' '.$border );

	}
    
public function resizeImg($orig, $new,  $new_width, $new_height, $ow, $oh)
{

// What sort of image?
$info = GetImageSize($orig);

if(empty($info))
{
  return false;
}


$width = $info[0];
$height = $info[1];
$mime = $info['mime'];

$type = substr(strrchr($mime, '/'), 1);

switch ($type)
{
case 'jpeg':
    $image_create_func = 'ImageCreateFromJPEG';
    $image_save_func = 'ImageJPEG';
	$new_image_ext = 'jpg';
    break;

case 'png':
    $image_create_func = 'ImageCreateFromPNG';
    $image_save_func = 'ImagePNG';
	$new_image_ext = 'png';
    break;

case 'bmp':
    $image_create_func = 'ImageCreateFromBMP';
    $image_save_func = 'ImageBMP';
	$new_image_ext = 'bmp';
    break;

case 'gif':
    $image_create_func = 'ImageCreateFromGIF';
    $image_save_func = 'ImageGIF';
	$new_image_ext = 'gif';
    break;

case 'vnd.wap.wbmp':
    $image_create_func = 'ImageCreateFromWBMP';
    $image_save_func = 'ImageWBMP';
	$new_image_ext = 'bmp';
    break;

case 'xbm':
    $image_create_func = 'ImageCreateFromXBM';
    $image_save_func = 'ImageXBM';
	$new_image_ext = 'xbm';
    break;

default:
	$image_create_func = 'ImageCreateFromJPEG';
    $image_save_func = 'ImageJPEG';
	$new_image_ext = 'jpg';
}

	// New Image
	
	$image_c = ImageCreateTrueColor($new_width, $new_height);
	$new_image = $image_create_func($orig);
	
	 if($type == "gif" or $type == "png"){
    imagecolortransparent($image_c, imagecolorallocatealpha($image_c, 0, 0, 0, 127));
    imagealphablending($image_c, false);
    imagesavealpha($image_c, true);
	}
	
	ImageCopyResampled($image_c, $new_image, 0, 0, 0, 0, $new_width, $new_height, $ow, $oh);
	// clean, debug:
	//@ob_get_clean(); @ob_get_clean(); @ob_get_clean(); @ob_get_clean(); @ob_get_clean(); @ob_get_clean(); @ob_get_clean(); 
	//header('Content-Type: image/jpeg');
		ob_start(); 
	//$process = $image_save_func($image_c, $new);
	$process = $image_save_func($image_c);
	$data = ob_get_clean(); 
	jimport( 'joomla.filesystem.file' );
	 @JFile::write($new, $data); 

	//	$process = $image_save_func($image_c, $new);
	
	}
	
	
 	public function op_show_image(&$image, $extra, $width, $height, $type)
	{
	  
	if (empty($image))
	{
	  if (!empty($width)) $w = 'width: '.$width.';'; else $w = ''; 
	  if (!empty($height)) $h = 'height: '.$height.';'; else $h = ''; 
	  return '<div style="'.$w.' '.$h.' ">&nbsp;</div>';
	}
	

		$class = '';
	   $alt = ''; 
	       $img = $this->op_image_info_array($image, 'class="'.$class.'" border="0" title="'.$alt.'" alt="'.$alt.'"', 1, $type, $width, $height);
           
           $real_height = $img['height'];
           $real_width =  $img['width']; 
		   $width = (int)$width; 
		   $height = (int)$height;
		   $real_width = (int)$real_width;
		   $real_height = (int)$real_height; 
		   if (empty($width)) $width = $real_width;
		   if (empty($height)) $height = $real_height;
           $w1 = floor((abs($real_width-$width))/2);
		   
           $w2 = $width-floor((abs($real_width-$width))/2);
           
           $h1 = floor((abs($real_height-$height))/2);
           $h2 = $height-floor((abs($real_height-$height))/2);
           
           $w3 = $width-$w1;
           $ret = '<div style="height: '.$height.'px; width: '.$width.'px; ">
           <div style="float: left; width: '.$w1.'px; height: 100%;"></div>
		   <div style="float: left; width: '.$w3.'px; height: '.$h1.'px;"></div>
           <div style="float: left; width: '.$w3.'px; height: '.$h2.'px;">';
           if (!empty($href)) $ret .= '<a href="'.$href.'" title="'.$alt.'">';
			$ret .= '<img src="'.$img['iurl'].'" width="'.$img['width'].'" height="'.$img['height'].'" />'; 
           if (!empty($href)) $ret .= '</a>';
           $ret .= '
           </div>
           </div>';
           
           return $ret; 

	  
	  
	}

}
