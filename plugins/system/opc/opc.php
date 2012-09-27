<?php
/**
 * @version		$Id: sef.php 21097 2011-04-07 15:38:03Z dextercowley $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');



/**
 * Joomla! SEF Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.sef
 */
class plgSystemOpc extends JPlugin
{
    public function onAfterRoute() {
	
	
	
	  $app = JFactory::getApplication();
	  if (!defined('JPATH_OPC'))
	  define('JPATH_OPC', JPATH_SITE.DS.'components'.DS.'com_onepage'); 
	  $format = JRequest::getVar('format', 'html'); 
	  
	  //if (stripos($format, 'html')!==false)
	  if('com_virtuemart' == JRequest::getCMD('option') && !$app->isAdmin()) {
	  $controller = JRequest::getWord('controller', JRequest::getWord('view', 'virtuemart'));
	  $view = JRequest::getWord('view', 'virtuemart'); 
	  
	  //  if ($view == 'cart')
		{
             // require_once(dirname(__FILE__) . DS . 'loader.php');
			 include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php'); //overrides'.DS.'vmplugin.php'); 
             include_once(JPATH_OPC.DS.'overrides'.DS.'virtuemart.cart.view.html.php'); 
			 include_once(JPATH_OPC.DS.'overrides'.DS.'vmplugin.php'); 
			 //include_once(JPATH_OPC.DS.'overrides'.DS.'cart.php'); 
			 
			 
		}	 
			 
			 
		if ($controller =='opc')
	    {
	     if (strpos($controller, '..')!==false) die('?'); 
	     require_once(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'controllers'.DS.'opc.php'); 
		 
	    }
			 // We need to fix a VM206 bugs when a new shipping address cannot be added, savecartuser
		if(('com_virtuemart' == JRequest::getCMD('option') && !$app->isAdmin()) && ('user'==JRequest::getCMD('view') && ('savecartuser' == JRequest::getCMD('task')) ))
		{
		  
		   if ('ST' == JRequest::getCMD('address_type'))
		   {
		     if (!isset($_POST['shipto_virtuemart_userinfo_id']))
			  {
			    $_POST['shipto_virtuemart_userinfo_id'] = '0'; 
				JRequest::setVar('shipto_virtuemart_userinfo_id', 0); 
	
			  }
		     /*
			<input type="hidden" name="option" value="com_virtuemart" />
			<input type="hidden" name="view" value="user" />
			<input type="hidden" name="controller" value="user" />
			<input type="hidden" name="task" value="savecartuser" />
			<input type="hidden" name="address_type" value="ST" />
			*/
		   }
		   // this fixes vm206 bug: Please enter your name. after changing BT address
		   if ('BT' == JRequest::getCMD('address_type'))
		   {
		     $user = JFactory::getUser();
			 //var_dump($user); die();
		     if (!isset($_POST['name']))
			  {
			    $_POST['name'] = $user->get('first_name', '').' '.$user->get('middle_name', '').' '.$user->get('last_name', ''); 
			  }
			 
		   }
		   
		 }
		

          }
	
	}
	
	public function onAfterDispatch()
	{
	  $app = JFactory::getApplication();
	 
	  
	  
	  //if (stripos($format, 'html')!==false)
	  if('com_virtuemart' == JRequest::getCMD('option') && !$app->isAdmin()) {
	    JHTML::script('onepageiframe.js', 'components/com_onepage/assets/js/', false);
	  }
	 
	 
	  
	}
	
	/**
	 * Converting the site URL to fit to the HTTP request
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($app->getName() != 'site') {
			return true;
		}
		 if(('com_virtuemart' == JRequest::getCMD('option') && !$app->isAdmin()) && ('cart'==JRequest::getCMD('view'))) {
		//Replace src links
		$base	= JURI::base(true).'/';
		$buffer = JResponse::getBody();
		 //orig opc: 
		 //$buffer = str_replace('$(".virtuemart_country_id").vm2front("list",{dest : "#virtuemart_state_id",ids : ""});', '$("#virtuemart_country_id").vm2frontOPC("list",{dest : "#virtuemart_state_id",ids : ""});'."\n".'$("#shipto_virtuemart_country_id").vm2frontOPC("list",{dest : "#shipto_virtuemart_state_id",ids : ""});', $buffer); 
		 $buffer = str_replace('$(".virtuemart_country_id").vm2front("list",{dest : "#virtuemart_state_id",ids : ""});', '', $buffer); 
		 $buffer = str_replace('$("select.virtuemart_country_id").vm2front("list",{dest : "#virtuemart_state_id",ids : ""});', '', $buffer); 
		 $buffer = str_replace('vm2front', 'vm2frontOPC', $buffer); 
		 $inside = JRequest::getVar('insideiframe', ''); 
		 if (!empty($inside))
		  {
		    $buffer = str_replace('<body', '<body onload="javascript: return parent.resizeIframe(document.body.scrollHeight);"', $buffer); 
		  }
		 //$buffer = str_replace('$(".virtuemart_country_id").vm2front("list",{dest : "#virtuemart_state_id",ids : ""});', '', $buffer); 
		$buffer = str_replace('jQuery("input").click', 'jQuery(null).click', $buffer);
		JResponse::setBody($buffer);
		}

		
		
		return true;
	}

}
