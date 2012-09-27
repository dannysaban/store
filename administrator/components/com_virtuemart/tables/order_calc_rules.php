<?php
/**
*
* Order item table
*
* @package	VirtueMart
* @subpackage Orders Order Calculation Rules
* @author valérie Isaksen
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: order_calc_rules.php 5341 2012-01-31 07:43:24Z alatak $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTable'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmtable.php');

/**
 * Order calculation rules table class
 * The class is is used to manage the order items in the shop.
 *
 * @package	VirtueMart
 * @author Valerie Isaksen
 */
class TableOrder_calc_rules extends VmTable {

	/** @var int Primary key */
	var $virtuemart_order_calc_rule_id = 0;
	/** @var int Order ID */
	var $virtuemart_order_id = NULL;

	/** @var int Vendor ID */
	var $virtuemart_vendor_id = NULL;

	/** @var string Calculation Rule name name */
	var $calc_rule_name = NULL;
	/** @var int Product Quantity */
	var $calc_kind = NULL;
	/** @var decimal Product item price */
	var $calc_amount = 0.00000;



	/**
	 * @param $db Class constructor; connect to the database
	 */
	function __construct($db) {
		parent::__construct('#__virtuemart_order_calc_rules', 'virtuemart_order_calc_rule_id', $db);

		$this->setLoggable();
	}

}
// pure php no closing tag
