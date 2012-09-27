<?php
/**
 * OPC script file
 *
 * This file is executed during install/upgrade and uninstall
 *
 * @author stAn
 * @package One Page Checkout
 */
defined('_JEXEC') or die('Restricted access');

//Maybe it is possible to set this within the xml file note by Max Milbers
@ini_set( 'memory_limit', '128M' );
@ini_set( 'max_execution_time', '1200' );

jimport( 'joomla.application.component.model');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// hack to prevent defining these twice in 1.6 installation
if (!defined('_OPC_SCRIPT_INCLUDED')) {
	define('_OPC_SCRIPT_INCLUDED', true);


	/**
	 * OPC custom installer class
	 */
	class com_onepageInstallerScript {



		/**
		 * Install script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function install () {

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');
		$path = JPATH_SITE.DS.'components'.DS.'com_onepage';

		jimport('joomla.installer.installer');
		$installer = & JInstaller::getInstance();

		$source 	= $installer->getPath('source');
		// installs the themes
		if (substr($source, strlen($source)) != DS) $source .= DS;

		if (!file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc'))
		 if (@JFolder::create(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc')===false)
		  {
		    echo 'Cannot create OPC plugin directory in /plugins/system/opc/'; 
		  }
		
		if (@JArchive::extract($source.'opcsystem.zip',JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc'.DS)===false)
		{
		  echo 'Cannot extract OPC system plugin to /plugins/system/opc'; 
		}
		
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config'))
		 {
		  if (@JFolder::create(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config')===false)
		   echo 'Cannot create config directory in /components/com_onepage/config'; 
		 }
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'))
		 {
		   JFile::copy($source.'admin'.DS.'default'.DS.'onepage.cfg.php', JPATH_SITE.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
		 }
	        
		$db =& JFactory::getDBO(); 
		$q = "select * from #__extensions where name = 'plg_system_onepage'";
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (empty($res))
		{
		$q = ' INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES ';
		 /*
		 $q .= " (NULL, 'plg_system_onepage', 'plugin', 'opc', 'vmextended', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_onepage\",\"type\":\"plugin\",\"creationDate\":\"December 2011\",\"author\":\"RuposTel s.r.o.\",\"copyright\":\"RuposTel s.r.o.\",\"authorEmail\":\"admin@rupostel.com\",\"authorUrl\":\"www.rupostel.com\",\"version\":\"1.7.0\",\"description\":\"One Page Checkout for VirtueMart 2\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0), "; 	
		 */
		 $q .= " (NULL, 'plg_system_onepage', 'plugin', 'opc', 'system', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_onepage\",\"type\":\"plugin\",\"creationDate\":\"December 2011\",\"author\":\"RuposTel s.r.o.\",\"copyright\":\"RuposTel s.r.o.\",\"authorEmail\":\"admin@rupostel.com\",\"authorUrl\":\"www.rupostel.com\",\"version\":\"1.7.0\",\"description\":\"One Page Checkout for VirtueMart 2\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0) "; 
		$db->setQuery($q); 
		$db->query(); 
		}
		else
		{
		 if (count($res)>1) echo 'More then one instance of onepage system plugin found! Please delete one of them manually.'; 
		}

		return true;
		}


		/**
		 * Update script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function update () {
			jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');
		$path = JPATH_SITE.DS.'components'.DS.'com_onepage'.DS;

		jimport('joomla.installer.installer');
		$installer = JInstaller::getInstance();

		$source 	= $installer->getPath('source');
		// installs the themes
		if (substr($source, strlen($source)) != DS) $source .= DS;
		 
		if (!file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc'))
		 JFolder::create(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc'); 
		
		if (JArchive::extract($source.'opcsystem.zip',JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc'.DS)===false)
		{
		  echo 'Cannot extract OPC system plugin'; 
		}
		$db =& JFactory::getDBO(); 
		$q = 'select * from #__extensions where element = "opc" limit 999'; 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		if (empty($res))
		{
		
		$q = ' INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES ';
		$q .= " (NULL, 'plg_system_onepage', 'plugin', 'opc', 'system', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_onepage\",\"type\":\"plugin\",\"creationDate\":\"December 2011\",\"author\":\"RuposTel s.r.o.\",\"copyright\":\"RuposTel s.r.o.\",\"authorEmail\":\"admin@rupostel.com\",\"authorUrl\":\"www.rupostel.com\",\"version\":\"1.7.0\",\"description\":\"One Page Checkout for VirtueMart 2\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0) "; 
		$db->setQuery($q); 
		$db->query(); 
		}
		

			return true; 
		}



		/**
		 * Uninstall script
		 * Triggers before database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function uninstall ($parent=null) {
			jimport('joomla.filesystem.folder');
		    jimport('joomla.filesystem.file');
		    jimport('joomla.filesystem.archive');
			 
			//@JFolder::delete(JPATH_SITE.DS.'plugins'.DS.'vmextended'.DS.'opc'); 
			@JFolder::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc');
			
			$db =& JFactory::getDBO(); 
			$q = "delete from #__extensions where element = 'opc' limit 5"; 
			$db->setQuery($q); 
			$db->query(); 
			
			$q = "delete from #__assets where alias = 'com-onepage'"; 
			$db->setQuery($q); 
			$db->query(); 
			
			return true;
		}

		/**
		 * Post-process method (e.g. footer HTML, redirect, etc)
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 */
		public function postflight ($type, $parent=null) {
			

			return true;
		}

		

	}

	/**
	 * Legacy j1.5 function to use the 1.6 class install/update
	 *
	 * @return boolean True on success
	 * @deprecated
	 */
	function com_install() {
	 if(version_compare(JVERSION,'1.7.0','ge')) {
	 return true; 
// Joomla! 1.7 code here
} elseif(version_compare(JVERSION,'1.6.0','ge')) {
	 return true; 
// Joomla! 1.6 code here
} elseif(version_compare(JVERSION,'2.5.0','ge')) {
	 return true; 
// Joomla! 2.5 code here
} else {
// Joomla! 1.5 code here

	 ob_start(); 
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');
		$path = JPATH_SITE.DS.'components'.DS.'com_onepage'.DS;

		jimport('joomla.installer.installer');
		$installer = & JInstaller::getInstance();
		//var_dump($installer->_manifest->files); 
		$source 	= $installer->getPath('source');
		// installs the themes
		if (substr($source, strlen($source)) != DS) $source .= DS;
		if (@JArchive::extract($source.'opcsystem.zip',JPATH_SITE.DS.'plugins'.DS.'system'.DS)===false)
		{
		  echo 'Cannot extract OPC system plugin'; 
		}
		
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config'))
		 {
		  if (@JFolder::create(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config')===false)
		   echo 'Cannot create config directory in /components/com_onepage/config'; 
		 }
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'))
		 {
		   @JFile::copy($source.'admin'.DS.'default'.DS.'onepage.cfg.php', JPATH_SITE.DS.'com_onepage'.DS.'config'.DS.'onepage.cfg.php'); 
		 }
		
		
		// we need to fix a bug in VM2.0 and J1.5:
		$search = '$dispatcher->trigger(\'onVmSiteController\', $_controller);';
		$rep = '$dispatcher->trigger(\'onVmSiteController\', array($_controller));';
		$x = file_get_contents(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.php'); 
		if (strpos($x, $search)===false)
		 {
		   $x = str_replace($search, $rep, $x); 
		   JFile::copy(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.php', JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.orig.opc_bck.php'); 
		   JFile::write(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.php', $x); 
		 }
		
		$db =& JFactory::getDBO(); 
		/*
		$q = ' INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES ';
		 $q .= " (NULL, 'plg_system_onepage', 'plugin', 'opc', 'vmextended', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_onepage\",\"type\":\"plugin\",\"creationDate\":\"December 2011\",\"author\":\"RuposTel s.r.o.\",\"copyright\":\"RuposTel s.r.o.\",\"authorEmail\":\"admin@rupostel.com\",\"authorUrl\":\"www.rupostel.com\",\"version\":\"1.7.0\",\"description\":\"One Page Checkout for VirtueMart 2\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0), "; 	
		 $q .= " (NULL, 'plg_system_onepage', 'plugin', 'opc', 'system', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_onepage\",\"type\":\"plugin\",\"creationDate\":\"December 2011\",\"author\":\"RuposTel s.r.o.\",\"copyright\":\"RuposTel s.r.o.\",\"authorEmail\":\"admin@rupostel.com\",\"authorUrl\":\"www.rupostel.com\",\"version\":\"1.7.0\",\"description\":\"One Page Checkout for VirtueMart 2\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0) "; 
		*/
		$q = ' INSERT INTO `#__plugins` (`id`, `name`, `element`, `folder`, `access`, `ordering`, `published`, `iscore`, `client_id`, `checked_out`, `checked_out_time`, `params`) VALUES
		(NULL, \'System - Onepage\', \'opc\', \'system\', 0, -100, 1, 0, 0, 0, \'0000-00-00 00:00:00\', \'\') ';
		/*
		(NULL, \'Vmextended - Onepage\', \'opc\', \'vmextended\', 0, -100, 1, 0, 0, 0, \'0000-00-00 00:00:00\', \'\') ';
		*/
		$db->setQuery($q); 
		$db->query(); 
	$x = ob_get_clean(); 
	echo $x;
		 return true; 
		}
	}

	/**
	 * Legacy j1.5 function to use the 1.6 class uninstall
	 *
	 * @return boolean True on success
	 * @deprecated
	 */
	function com_uninstall() {
	 if(version_compare(JVERSION,'1.7.0','ge')) {
// Joomla! 1.7 code here
} elseif(version_compare(JVERSION,'1.6.0','ge')) {
// Joomla! 1.6 code here
} else {
// Joomla! 1.5 code here

			jimport('joomla.filesystem.folder');
		    jimport('joomla.filesystem.file');
		    jimport('joomla.filesystem.archive');
			if (file_exists(JPATH_SITE.DS.'plugins'.DS.'vmextended'.DS.'opc.xml'))
			JFile::delete(JPATH_SITE.DS.'plugins'.DS.'vmextended'.DS.'opc.xml'); 
			if (file_exists(JPATH_SITE.DS.'plugins'.DS.'vmextended'.DS.'opc.php'))
			JFile::delete(JPATH_SITE.DS.'plugins'.DS.'vmextended'.DS.'opc.php');
			if (file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc.xml'))
			JFile::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc.xml');
			if (file_exists(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc.php'))
			JFile::delete(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'opc.php');
			if (file_exists(JPATH_SITE.DS.'components'.DS.'themes'))
			if (@JFolder::remove(JPATH_SITE.DS.'components'.DS.'themes')===false)
			 echo 'Cannot remove themes directory! Please remove it manually from /components/com_onepage'; 
			
			$db =& JFactory::getDBO(); 
			$q = "delete from #__plugins where element = 'opc' limit 5"; 
			$db->setQuery($q); 
			$db->query(); 
			return true;
}
	}

} // if(defined)


