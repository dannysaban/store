<?php
/**
 *
 * Data module for shop users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 * @author	RickG
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: user.php 5707 2012-03-27 20:34:53Z Milbo $
 */


if(!class_exists('VirtuemartModelUser')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'user.php');
/**
 * Model class for shop users
 *
 * @package	VirtueMart
 * @subpackage	User
 * @author	RickG
 * @author Max Milbers
 */
class OPCUsermodel extends VirtueMartModelUser {
 	
	/**
	 * Internal function of VM which must be overrided to support user data association without login when duplicit email is found
	 *
	 * @param unknown_type $id
	 */
	public function setUserId($id){

		$app = JFactory::getApplication();
		// 		if($app->isAdmin()){
		if($this->_id!=$id){
			$this->_id = (int)$id;
			$this->_data = null;
		}
		// 		}
	}
}