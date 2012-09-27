<?php
/**
*
* Lists all the categories in the shop
*
* @package	VirtueMart
* @subpackage Category
* @author RickG, jseros, RolandD, Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 5710 2012-03-28 15:28:22Z electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea();

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="header">
<div id="filterbox">
	<table class="">
		<tr>
			<td align="left">
			<?php echo $this->displayDefaultViewSearch() ?>
			</td>

		</tr>
	</table>
	</div>
	<div id="resultscounter"><?php echo $this->pagination->getResultsCounter(); ?></div>

</div>


	<div id="editcell">
		<table class="adminlist" cellspacing="0" cellpadding="0">
		<thead>
		<tr>

			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->categories); ?>);" />
			</th>
			<th align="left">
				<?php echo $this->sort('category_name') ?>
			</th>
			<th align="left">
				<?php echo $this->sort('category_description', 'COM_VIRTUEMART_DESCRIPTION') ; ?>
			</th>
			<th align="left" width="11%">
				<?php echo JText::_('COM_VIRTUEMART_PRODUCT_S'); ?>
			</th>

			<!-- Commented out for future use
			<th width="5%">
				<?php echo $this->sort( 'cx.category_shared' , 'COM_VIRTUEMART_PRODUCT_LIST_SHARED') ?>
			</th>
			-->
			<th align="left" width="13%">
				<?php echo $this->sort( 'c.ordering' , 'COM_VIRTUEMART_ORDERING') ?>
				<?php echo JHTML::_('grid.order', $this->categories, 'filesave.png', 'saveOrder' ); ?>
			</th>
			<th align="center" width="5%">
				<?php echo $this->sort('c.published' , 'COM_VIRTUEMART_PUBLISHED') ?>
			</th>
			  <th><?php echo $this->sort('virtuemart_category_id', 'COM_VIRTUEMART_ID')  ?></th>

		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		$repeat = 0;

 		$nrows = count( $this->categories );

		if( $this->pagination->limit < $nrows ){
			if( ($this->pagination->limitstart + $this->pagination->limit) < $nrows ) {
				$nrows = $this->pagination->limitstart + $this->pagination->limit;
			}
		}

// 		for ($i = $this->pagination->limitstart; $i < $nrows; $i++) {

		foreach($this->categories as $i=>$cat){

// 			if( !isset($this->rowList[$i])) $this->rowList[$i] = $i;
// 			if( !isset($this->depthList[$i])) $this->depthList[$i] = 0;

// 			$row = $this->categories[$this->rowList[$i]];

			$checked = JHTML::_('grid.id', $i, $cat->virtuemart_category_id);
			$published = JHTML::_('grid.published', $cat, $i);
			$editlink = JRoute::_('index.php?option=com_virtuemart&view=category&task=edit&cid=' . $cat->virtuemart_category_id);
// 			$statelink	= JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $cat->virtuemart_category_id);
			$showProductsLink = JRoute::_('index.php?option=com_virtuemart&view=product&virtuemart_category_id=' . $cat->virtuemart_category_id);

			$categoryLevel = '';
			$repeat = $cat->level;

			if($repeat > 1){
				$categoryLevel = str_repeat(".&nbsp;&nbsp;&nbsp;", $repeat - 1);
				$categoryLevel .= "<sup>|_</sup>&nbsp;";
			}
		?>
			<tr class="<?php echo "row".$k;?>">

				<td><?php echo $checked;?></td>
				<td align="left">
					<span class="categoryLevel"><?php echo $categoryLevel;?></span>
					<a href="<?php echo $editlink;?>"><?php echo $this->escape($cat->category_name);?></a>
				</td>
				<td align="left">
					<?php echo $cat->category_description; ?>
				</td>
				<td>
					<?php echo  $this->model->countProducts($cat->virtuemart_category_id);//ShopFunctions::countProductsByCategory($row->virtuemart_category_id);?>
					&nbsp;<a href="<?php echo $showProductsLink; ?>">[ <?php echo JText::_('COM_VIRTUEMART_SHOW');?> ]</a>
				</td>

<?php
		/*
		 * html comment do a bug in some server
		 * Used in the future Notice by Patrick Kohl
				<td align="center">
					<a href="#" onclick="return listItemTask('cb<?php echo $i;?>', 'toggleShared')" title="<?php echo ( $row->category_shared == 'Y' ) ?JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>">
						<img src="images/<?php echo ( $row->category_shared) ? 'tick.png' : 'publish_x.png';?>" width="16" height="16" border="0" alt="<?php echo ( $row->category_shared == 'Y' ) ? JText::_('COM_VIRTUEMART_YES') : JText::_('COM_VIRTUEMART_NO');?>" />
					</a>
				</td>
				*/
?>
				<td align="center" class="order">
					<span><?php echo $this->pagination->orderUpIcon( $i, ($cat->category_parent_id == 0 || $cat->category_parent_id == @$this->categories[$this->rowList[$i - 1]]->category_parent_id), 'orderUp', JText::_('COM_VIRTUEMART_MOVE_UP')); ?></span>
					<span><?php echo $this->pagination->orderDownIcon( $i, $nrows, ($cat->category_parent_id == 0 || $cat->category_parent_id == @$this->categories[$this->rowList[$i + 1]]->category_parent_id), 'orderDown', JText::_('COM_VIRTUEMART_MOVE_DOWN')); ?></span>
					<input class="ordering" type="text" name="order[<?php echo $i?>]" id="order[<?php echo $i?>]" size="5" value="<?php echo $cat->ordering; ?>" style="text-align: center" />
				</td>
				<td align="center">
					<?php echo $published;?>
				</td>
				<td><?php echo $cat->virtuemart_category_id; // echo $product->vendor_name; ?></td>
			</tr>
		<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
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
