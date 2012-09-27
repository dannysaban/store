<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* This is the default Basket Template. Modify as you like.
*
* @version $Id: basket_b2c.html.php 1377 2008-04-19 17:54:45Z gregdev $
* @package VirtueMart
* @subpackage templates
* @copyright Copyright (C) 2004-2005 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
?>
<table width="100%" cellspacing="0" cellpadding="4" border="0" style="border:1px solid #BEBCB7;margin-bottom:10px;">

  <tr align="left" >
        <th class="vmsectiontableheader"><?php echo $VM_LANG->_('PHPSHOP_CART_NAME') ?></th>
        <th class="vmsectiontableheader"><?php echo $VM_LANG->_('PHPSHOP_CART_SKU') ?></th>
	<th class="vmsectiontableheader"><?php echo $VM_LANG->_('PHPSHOP_CART_PRICE') ?></th>
	<th class="vmsectiontableheader"><?php echo $VM_LANG->_('PHPSHOP_CART_QUANTITY') ?> / <?php echo $VM_LANG->_('PHPSHOP_CART_ACTION') ?></th>
	<th class="vmsectiontableheader"><?php echo $VM_LANG->_('PHPSHOP_CART_SUBTOTAL') ?></th>
  </tr>
<?php foreach( $product_rows as $product ) { ?>
  <tr valign="top" class="<?php echo $product['row_color'] ?>">
	<td><?php echo $product['product_name'] . $product['product_attributes'] ?></td>
	<td><?php echo $product['product_sku'] ?></td>
	<td align="right"><?php echo $product['product_price'] ?></td>
	<td><?php echo $product['update_form'] ?>
		<?php echo $product['delete_form'] ?>
	</td>
    <td align="right"><?php echo $product['subtotal'] ?></td>
  </tr>
<?php } ?>
<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
  <tr class="vmsectiontableentry1">
    <td colspan="4" align="right"><?php echo $VM_LANG->_('PHPSHOP_CART_SUBTOTAL') ?>:</td> 
    <td colspan="3" align="right"><?php echo $subtotal_display ?></td>
  </tr>
<?php if( $discount_before ) { ?>
  <tr class="vmsectiontableentry1">
    <td colspan="4" align="right"><?php echo $VM_LANG->_('PHPSHOP_COUPON_DISCOUNT') ?>:
    </td> 
    <td colspan="3" align="right"><?php echo $coupon_display ?></td>
  </tr>
<?php } 
if( $shipping ) { ?>
  <tr class="vmsectiontableentry1">
	<td colspan="4" align="right"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_SHIPPING') ?>: </td> 
	<td colspan="3" align="right"><?php echo $shipping_display ?></td>
  </tr>
<?php } 
if($discount_after) { ?>
  <tr class="vmsectiontableentry1">
    <td colspan="4" align="right"><?php echo $VM_LANG->_('PHPSHOP_COUPON_DISCOUNT') ?>:
    </td> 
    <td colspan="3" align="right"><?php echo $coupon_display ?></td>
  </tr>
<?php } ?>
  <tr>
    <td colspan="4">&nbsp;</td>
    <td colspan="3"><hr /></td>
  </tr>
  <tr>
    <td class="vmsectiontableentry2"colspan="4" align="right"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_TOTAL') ?>: </td>
    <td class="vmsectiontableentry2"colspan="3" align="right"><strong><?php echo $order_total_display ?></strong></td>
  </tr>
<?php if ( $show_tax ) { ?>
  <tr class="vmsectiontableentry1">
        <td colspan="4" align="right" valign="top"><?php echo $VM_LANG->_('PHPSHOP_ORDER_PRINT_TOTAL_TAX') ?>: </td> 
        <td colspan="3" align="right""><?php echo $tax_display ?></td>
  </tr>
<?php } ?>
  <tr>
    <td colspan="7"><hr /></td>
  </tr>
</table>
