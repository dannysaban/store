<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
*
* @version $Id: default.php 6058 2012-06-06 08:19:35Z alatak $
* @package VirtueMart
* @subpackage Report
* @copyright Copyright (C) VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

AdminUIHelper::startAdminArea();
/* Load some variables */
$rows = count( $this->report );
$intervalTitle = JRequest::getVar('intervals','day');
if ( ($intervalTitle =='week') or ($intervalTitle =='month') ) $addDateInfo = true ;
else $addDateInfo = false;

// if( $this->pagination->limit < $rows ){
	// if( ($this->pagination->limitstart + $this->pagination->limit) < $rows ) {
		// $rows = $this->pagination->limitstart + $this->pagination->limit;
	// }
// }
if ( JVM_VERSION == 2 )
	JHtml::_('behavior.framework', true);
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="header">
        <h2><?php echo JText::sprintf('COM_VIRTUEMART_REPORT_TITLE', vmJsApi::date( $this->from_period, 'LC',true) , vmJsApi::date( $this->until_period, 'LC',true) ); ?></h2>
        <div id="filterbox">

            <table>
                <tr>
                    <td align="left" width="100%">
						<?php echo JText::_('COM_VIRTUEMART_ORDERSTATUS').':'. $this->lists['state_list']; ?>
						<?php echo JText::_('COM_VIRTUEMART_REPORT_INTERVAL').':'. $this->lists['intervals']; ?>
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_SET_PERIOD') . $this->lists['select_date'];

                    echo JText::_('COM_VIRTUEMART_REPORT_FROM_PERIOD') .  vmJsApi::jDate($this->from_period, 'from_period');
                   echo JText::_('COM_VIRTUEMART_REPORT_UNTIL_PERIOD') . vmJsApi::jDate($this->until_period, 'until_period');
                        if(VmConfig::get('multix','none')!='none'){
                        	$vendorId = JRequest::getInt('virtuemart_vendor_id',1);
                        	echo ShopFunctions::renderVendorList($vendorId,false);
                        } ?>
                        <button onclick="this.form.period.value='';this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?>
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="resultscounter">
            <?php echo $this->pagination->getResultsCounter();?>
        </div>
    </div>

    <div id="editcell">
        <table class="adminlist" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>
                        <?php echo $this->sort('created_on', 'COM_VIRTUEMART_'.$intervalTitle); ?>
                    </th>
                    <th>
                        <?php echo $this->sort('o.virtuemart_order_id', 'COM_VIRTUEMART_REPORT_BASIC_ORDERS') ; ?>
                    </th>
                    <th>
                        <?php echo $this->sort('product_quantity', 'COM_VIRTUEMART_REPORT_BASIC_TOTAL_ITEMS') ; ?>
                    </th>
                    <th>
                        <?php echo $this->sort('order_subtotal', 'COM_VIRTUEMART_REPORT_BASIC_REVENUE') ; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
	    $i = 0;
	    for ($j =0; $j < $rows; ++$j ){
	    	$r = $this->report[$j];

	    	//$is = $this->itemsSold[$j];
	    	$s = 0;
	    	?>
                <tr class="row<?php echo $i;?>">
                    <td align="center">
                        <?php echo $r['intervals'] ;
						if ( $addDateInfo ) {
							echo ' ('.substr ( $r['created_on'],0,4 ).')';
						}
                     ?>
                    </td>
                    <td align="center">
                        <?php echo $r['count_order_id'];?>
                    </td>
                    <td align="center">
                        <?php echo $r['product_quantity'];?>
                    </td>
                    <td align="right">
                        <?php echo $r['order_subtotal'];?>
                    </td>
                </tr>
                <?php
	    	$i = 1-$i;
	    }
	    ?>
            </tbody>
           <thead>
                <tr>
                    <th  class="right"><?php echo JText::_('COM_VIRTUEMART_TOTAL').' : '; ?></th>
                    <th><?php echo $this->totalReport['number_of_ordersTotal']?></th>
                    <th><?php echo $this->totalReport['itemsSoldTotal'];?></th>
                    <th class="right"><?php echo $this->totalReport['revenueTotal'];?></th>
				</tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="10">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

	<?php echo $this->addStandardHiddenToForm(); ?>
</form>

<?php AdminUIHelper::endAdminArea(); ?>

