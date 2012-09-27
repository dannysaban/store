<?php
/**
 *
 * View class for the product
 *
 * @package	VirtueMart
 * @subpackage
 * @author
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 6306 2012-08-06 14:19:51Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author RolandD,Max Milbers
 */
if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');

class VirtuemartViewProduct extends VmView {

	function display($tpl = null) {

		// Get the task
		$task = JRequest::getWord('task');

		// Load helpers
		$this->loadHelper('currencydisplay');


		$this->loadHelper('html');
		$this->loadHelper('image');


		//$category_model = VmModel::getModel('category');
		$model = VmModel::getModel();

		// Handle any publish/unpublish
		switch ($task) {
			case 'add':
			case 'edit':

				//this was in the controller for the edit tasks, I dont know if it is still needed,
				$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'product'.DS.'tmpl');

				$virtuemart_product_id = JRequest::getVar('virtuemart_product_id', array());

				if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
					$virtuemart_product_id = (int)$virtuemart_product_id[0];
				} else {
					$virtuemart_product_id = (int)$virtuemart_product_id;
				}

				$product = $model->getProductSingle($virtuemart_product_id,false);
				$product_parent= $model->getProductParent($product->product_parent_id);

				$mf_model = VmModel::getModel('manufacturer');
				$manufacturers = $mf_model->getManufacturerDropdown($product->virtuemart_manufacturer_id);
				$this->assignRef('manufacturers',	$manufacturers);

				// Get the category tree
				if (isset($product->categories)) $category_tree = ShopFunctions::categoryListTree($product->categories);
				else $category_tree = ShopFunctions::categoryListTree();
				$this->assignRef('category_tree', $category_tree);

				//Get the shoppergoup list - Cleanshooter Custom Shopper Visibility
				if (isset($product->shoppergroups)) $shoppergroupList = ShopFunctions::renderShopperGroupList($product->shoppergroups, true);
				$this->assignRef('shoppergroupList', $shoppergroupList);

				// Load the product price
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$product->prices = $calculator -> getProductPrices($product);

				$product_childIds = $model->getProductChildIds($virtuemart_product_id);

				$product_childs = array();
				foreach($product_childIds as $id){
					$product_childs[] = $model->getProductSingle($id,false);
				}
				$this->assignRef('product_childs', $product_childs);

				$DBTax = ''; 	//JText::_('COM_VIRTUEMART_RULES_EFFECTING') ;
				foreach($calculator->rules['DBTax'] as $rule){
					$DBTax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('DBTaxRules', $DBTax);

				$tax = ''; //JText::_('COM_VIRTUEMART_TAX_EFFECTING').'<br />';
				foreach($calculator->rules['Tax'] as $rule){
					$tax .= $rule['calc_name']. '<br />';
				}
				foreach($calculator->rules['VatTax'] as $rule){
					$tax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('taxRules', $tax);

				$DATax = '';//JText::_('COM_VIRTUEMART_RULES_EFFECTING');
				foreach($calculator->rules['DATax'] as $rule){
					$DATax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('DATaxRules', $DATax);

// 				$this->assignRef('override', $calculator->override);
// 				$this->assignRef('product_override_price', $calculator->product_override_price);

				if(!isset($product->product_tax_id)){
					$product->product_tax_id=0;
				}
				$lists['taxrates'] = ShopFunctions::renderTaxList($product->product_tax_id,'product_tax_id');
				if(!isset($product->product_discount_id)){
					$product->product_discount_id=0;
				}
				$lists['discounts'] = $this -> renderDiscountList($product->product_discount_id);

				if(!class_exists('VirtueMartModelConfig')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'config.php');
				$productLayouts = VirtueMartModelConfig::getLayoutList('productdetails');
				$this->assignRef('productLayouts', $productLayouts);

				// Load Images
				$model->addImages($product);

				if(is_Dir(VmConfig::get('vmtemplate').DS.'images'.DS.'availability'.DS)){
					$imagePath = VmConfig::get('vmtemplate').'/images/availability/';
				} else {
					$imagePath = '/components/com_virtuemart/assets/images/availability/';
				}
				$this->assignRef('imagePath', $imagePath);

				// Load the vendors
				$vendor_model = VmModel::getModel('vendor');

				// 				$vendors = $vendor_model->getVendors();
				// 				$lists['vendors'] = JHTML::_('select.genericlist', $vendors, 'virtuemart_vendor_id', '', 'virtuemart_vendor_id', 'vendor_name', $product->virtuemart_vendor_id);

				if(Vmconfig::get('multix','none')!=='none'){
					$lists['vendors'] = Shopfunctions::renderVendorList($product->virtuemart_vendor_id);
				}
				// Load the currencies
				$currency_model = VmModel::getModel('currency');

				$vendor_model->setId(1);
				$vendor = $vendor_model->getVendor();
				if(empty($product->product_currency)){
					$product->product_currency = $vendor->vendor_currency;
				}
				$currencies = JHTML::_('select.genericlist', $currency_model->getCurrencies(), 'product_currency', '', 'virtuemart_currency_id', 'currency_name', $product->product_currency);
				$currency = $currency_model->getCurrency($product->product_currency);
				$this->assignRef('product_currency', $currency->currency_symbol);
				$currency = $currency_model->getCurrency($vendor->vendor_currency);
				$this->assignRef('vendor_currency', $currency->currency_symbol);


				if(count($manufacturers)>0 ){
					$lists['manufacturers'] = JHTML::_('select.genericlist', $manufacturers, 'virtuemart_manufacturer_id', 'class="inputbox"', 'value', 'text', $product->virtuemart_manufacturer_id );
				}

				$lists['product_weight_uom'] = ShopFunctions::renderWeightUnitList('product_weight_uom',$task=='add'? VmConfig::get('weight_unit_default'): $product->product_weight_uom);
				$lists['product_iso_uom'] = ShopFunctions::renderUnitIsoList('product_unit',$task=='add'? VmConfig::get('weight_unit_default'): $product->product_unit);
				$lists['product_lwh_uom'] = ShopFunctions::renderLWHUnitList('product_lwh_uom', $task=='add'?VmConfig::get('lwh_unit_default') : $product->product_lwh_uom);

				if( empty( $product->product_available_date )) {
					$product->product_available_date = date("Y-m-d") ;
				}
				$waitinglistmodel = VmModel::getModel('waitinglist');
				/* Load waiting list */
				if ($product->virtuemart_product_id) {
					//$waitinglist = $this->get('waitingusers', 'waitinglist');
					$waitinglist = $waitinglistmodel->getWaitingusers($product->virtuemart_product_id);
					$this->assignRef('waitinglist', $waitinglist);
				}
				$productShoppers = $model->getProductShoppersByStatus($product->virtuemart_product_id,array('S') );
				$this->assignRef('productShoppers', $productShoppers);
				$orderstatusModel = VmModel::getModel('orderstatus');
				$lists['OrderStatus'] = $orderstatusModel->renderOrderStatusList(array());
				$field_model = VmModel::getModel('customfields');
				$fieldTypes = $field_model->getField_types();
				$this->assignRef('fieldTypes', $fieldTypes);

				/* Load product types lists */
				$customsList = $field_model->getCustomsList();
				$customlist = JHTML::_('select.genericlist', $customsList,'customlist');
				$this->assignRef('customsList', $customlist);

				$ChildCustomRelation = $field_model->getProductChildCustomRelation();
				$this->assignRef('ChildCustomRelation',$ChildCustomRelation);


				if ($product->product_parent_id > 0) {

					$parentRelation= $field_model->getProductParentRelation($product->virtuemart_product_id);
					$this->assignRef('parentRelation',$parentRelation);

					// Set up labels
					$info_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_INFO_LBL');
					$status_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_STATUS_LBL');
					$dim_weight_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_DIM_WEIGHT_LBL');
					$images_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_IMAGES_LBL');
					$delete_message = JText::_('COM_VIRTUEMART_PRODUCT_FORM_DELETE_ITEM_MSG');
				}
				else {
					if ($task == 'add') $action = JText::_('COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT_LBL');
					else $action = JText::_('COM_VIRTUEMART_PRODUCT_FORM_UPDATE_ITEM_LBL');

					$info_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_INFO_LBL');
					$status_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_STATUS_LBL');
					$dim_weight_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL');
					$images_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_IMAGES_LBL');
					$delete_message = JText::_('COM_VIRTUEMART_PRODUCT_FORM_DELETE_PRODUCT_MSG');
				}


				$config = JFactory::getConfig();
				$tzoffset = $config->getValue('config.offset');

				$this->assignRef('tzoffset',	$tzoffset);

				// Assign the values
				$this->assignRef('pane', $pane);
				$this->assignRef('editor', $editor);

				$this->assignRef('product', $product);
				$this->assignRef('currencies', $currencies);
//no need moved to top $this->assignRef('manufacturers', $manufacturers);

				$this->assignRef('related_products', $related_products);

				$this->assignRef('product_parent', $product_parent);
				/* Assign label values */
				$this->assignRef('action', $action);
				$this->assignRef('info_label', $info_label);
				$this->assignRef('status_label', $status_label);
				$this->assignRef('dim_weight_label', $dim_weight_label);
				$this->assignRef('images_label', $images_label);
				$this->assignRef('delete_message', $delete_message);
				$this->assignRef('lists', $lists);
				// Toolbar
				$text="";
				if ($task == 'edit') {
					if ($product->product_sku) $sku=' ('.$product->product_sku.')'; else $sku="";

					if(!empty($product->virtuemart_product_id)){
						$text = '<a href="'.juri::root().'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'" target="_blank" >'. $product->product_name.$sku.'<span class="vm2-modallink"></span></a>';
					} else {
						$text = $product->product_name.$sku;
					}
				}
				$this->SetViewTitle('PRODUCT',$text);
				//JToolBarHelper::custom('sentproductemailtocustomer', 'email_32', 'email_32',  'COM_VIRTUEMART_PRODUCT_EMAILTOSHOPPER' ,false);
				$this->addStandardEditViewCommands ($product->virtuemart_product_id);
				break;

			default:
			if ($product_parent_id=JRequest::getInt('product_parent_id',false) ) {
				$product_parent= $model->getProduct($product_parent_id);
				$title='PRODUCT_CHILDREN_LIST' ;
				$link_to_parent =  JHTML::_('link', JRoute::_('index.php?view=product&task=edit&virtuemart_product_id='.$product_parent->virtuemart_product_id.'&option=com_virtuemart'), $product_parent->product_name, array('title' => JText::_('COM_VIRTUEMART_EDIT_PARENT').' '.$product_parent->product_name));
				$msg= JText::_('COM_VIRTUEMART_PRODUCT_OF'). " ".$link_to_parent;
			} else {
				$title='PRODUCT';
				$msg="";
			}
			$this->db = JFactory::getDBO();

			$this->SetViewTitle($title, $msg );

			$this->addStandardDefaultViewLists($model,'created_on');

			/* Get the list of products */
			$productlist = $model->getProductListing(false,false,false,false,true);

			//The pagination must now always set AFTER the model load the listing
			$pagination = $model->getPagination();
			$this->assignRef('pagination', $pagination);

			/* Get the category tree */
			$categoryId = $model->virtuemart_category_id; //OSP switched to filter in model, was JRequest::getInt('virtuemart_category_id');
			$category_tree = ShopFunctions::categoryListTree(array($categoryId));
			$this->assignRef('category_tree', $category_tree);

			// Check for Media Items and Reviews, set the price

			$media = VmModel::getModel('media');

			$productreviews = VmModel::getModel('ratings');

			/* Load the product price */
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');

			$vendor_model = VmModel::getModel('vendor');

			foreach ($productlist as $virtuemart_product_id => $product) {
				$product->mediaitems = count($product->virtuemart_media_id);
				$product->reviews = $productreviews->countReviewsForProduct($product->virtuemart_product_id);

				$vendor_model->setId($product->virtuemart_vendor_id);
				$vendor = $vendor_model->getVendor();

				$currencyDisplay = CurrencyDisplay::getInstance($vendor->vendor_currency,$vendor->virtuemart_vendor_id);

				if(!empty($product->product_price) && !empty($product->product_currency) ){
					$product->product_price_display = $currencyDisplay->priceDisplay($product->product_price,(int)$product->product_currency,1,true);
				}

				/* Write the first 5 categories in the list */
				$product->categoriesList = shopfunctions::renderGuiList('virtuemart_category_id','#__virtuemart_product_categories','virtuemart_product_id',$product->virtuemart_product_id,'category_name','#__virtuemart_categories','virtuemart_category_id','category');

			}

			$mf_model = VmModel::getModel('manufacturer');
			$manufacturers = $mf_model->getManufacturerDropdown();
			$this->assignRef('manufacturers',	$manufacturers);

			/* add Search filter in lists*/
			/* Search type */
			$options = array( '' => JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'),
		    				'parent' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_PARENT_PRODUCT'),
							'product' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRODUCT'),
							'price' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRICE'),
							'withoutprice' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_WITHOUTPRICE')
			);
			$this->lists['search_type'] = VmHTML::selectList('search_type', JRequest::getVar('search_type'),$options);

			/* Search order */
			$options = array( 'bf' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_BEFORE'),
								  'af' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_AFTER')
			);
			$this->lists['search_order'] = VmHTML::selectList('search_order', JRequest::getVar('search_order'),$options);

			// Toolbar

			//JToolBarHelper::save('sentproductemailtoshoppers', JText::_('COM_VIRTUEMART_PRODUCT_EMAILTOSHOPPERS'));
			JToolBarHelper::custom('createchild', 'new', 'new', JText::_('COM_VIRTUEMART_PRODUCT_CHILD'), true);
			JToolBarHelper::custom('cloneproduct', 'copy', 'copy', JText::_('COM_VIRTUEMART_PRODUCT_CLONE'), true);
			JToolBarHelper::custom('addrating', 'default', '', JText::_('COM_VIRTUEMART_ADD_RATING'), true);
			$this->addStandardDefaultViewCommands();


			$this->assignRef('productlist', $productlist);
			$this->assignRef('virtuemart_category_id', $categoryId);
			$this->assignRef('model', $model);

			break;
		}

		parent::display($tpl);
	}

	/**
	 * This is wrong
	 *@deprecated
	 */
	function renderMail() {
		$this->setLayout('mail_html_waitlist');
		$this->subject = JText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_SUBJECT', $this->productName);
		$notice_body = JText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_BODY', $this->productName, $this->url);

		parent::display();
	}


	/**
	 * Renders the list for the discount rules
	 *
	 * @author Max Milbers
	 */
	function renderDiscountList($selected,$before=false){

		if(!class_exists('VirtueMartModelCalc')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'calc.php');
		$discounts = VirtueMartModelCalc::getDiscounts();
		//		if($before){
		//			$discounts = VirtueMartModelCalc::getDBDiscounts();
		//		} else {
		//			$discounts = VirtueMartModelCalc::getDADiscounts();
		//		}

		$discountrates = array();
		$discountrates[] = JHTML::_('select.option', '-1', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NONE'), 'product_discount_id' );
		$discountrates[] = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NO_SPECIAL'), 'product_discount_id' );
		//		$discountrates[] = JHTML::_('select.option', 'override', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE'), 'product_discount_id');
		foreach($discounts as $discount){
			$discountrates[] = JHTML::_('select.option', $discount->virtuemart_calc_id, $discount->calc_name, 'product_discount_id');
		}
		$listHTML = JHTML::_('Select.genericlist', $discountrates, 'product_discount_id', '', 'product_discount_id', 'text', $selected );
		return $listHTML;

	}

	function displayLinkToChildList($product_id, $product_name) {

		//$this->db = JFactory::getDBO();
		$this->db->setQuery(' SELECT COUNT( * ) FROM `#__virtuemart_products` WHERE `product_parent_id` ='.$product_id);
		if ($result = $this->db->loadResult()){
			$result = JText::sprintf('COM_VIRTUEMART_X_CHILD_PRODUCT', $result);
			echo JHTML::_('link', JRoute::_('index.php?view=product&product_parent_id='.$product_id.'&option=com_virtuemart'), $result, array('title' => JText::sprintf('COM_VIRTUEMART_PRODUCT_LIST_X_CHILDREN',$product_name) ));
		}
	}

	function displayLinkToParent($product_parent_id) {

		//$this->db = JFactory::getDBO();
		$this->db->setQuery(' SELECT * FROM `#__virtuemart_products_'.VMLANG.'` as l JOIN `#__virtuemart_products` using (`virtuemart_product_id`) WHERE `virtuemart_product_id` = '.$product_parent_id);
		if ($parent = $this->db->loadObject()){
			$result = JText::sprintf('COM_VIRTUEMART_LIST_CHILDREN_FROM_PARENT', $parent->product_name);
			echo JHTML::_('link', JRoute::_('index.php?view=product&product_parent_id='.$product_parent_id.'&option=com_virtuemart'), $parent->product_name, array('title' => $result));
		}
	}

}

//pure php no closing tag
