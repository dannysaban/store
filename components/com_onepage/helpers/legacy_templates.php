<?php
 ob_start();  
  echo '<div id="vmMainPageOPC">'; 
 include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
 JHTML::stylesheet('onepage.css', 'components/com_onepage/themes/'.$selected_template.'/', array());
 JHTML::_('behavior.formvalidation');
 JHTML::stylesheet('vmpanels.css', JURI::root() . 'components/com_virtuemart/assets/css/');
 if (empty($this->cart) || (empty($this->cart->products)))
 {
   include(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'empty_cart.tpl.php'); 
 }
 else
 {
 extract($tpla);
 $VM_LANG = new op_languageHelper(); 
 $GLOBALS['VM_LANG'] = $VM_LANG; 
 $lang =& JFactory::getLanguage();
 $tag = $lang->getTag();
 $langcode = JRequest::getVar('lang', ''); 
 $no_jscheck = true;
 define("_MIN_POV_REACHED", '1');
 $no_jscheck = true;
 
 if (empty($langcode))
 {
 if (!empty($tag))
 {
 $arr = explode('-', $tag); 
 if (!empty($arr[0])) $langcode = $arr[0]; 
 }
 if (empty($langcode)) $langcode = 'en'; 
 }
 $GLOBALS['mosConfig_locale'] = $langcode; 

 // legacy vars to be deleted: 
 include(JPATH_ROOT.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
 $no_shipping = $op_disable_shipping; 
 
 
 
$cart = $this->cart;

 if (($this->logged($cart)))
 include(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'onepage.logged.tpl.php'); 
 else
 include(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'onepage.unlogged.tpl.php'); 
 }
 if (file_exists(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'include.php'))
 include(JPATH_OPC.DS.'themes'.DS.$selected_template.DS.'include.php'); 
 echo '</div>';
 
 $output = ob_get_clean(); 
 //post process
 $output = str_replace('name="adminForm"', ' id="opcform" name="adminForm"  ', $output);
 echo $output; 
?>