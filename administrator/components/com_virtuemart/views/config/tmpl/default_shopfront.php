<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author RickG
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_shopfront.php 6355 2012-08-20 09:23:27Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<br />
<table width="100%">
	<tr>
		<td valign="top" width="50%">
			<fieldset>
				<legend>

				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MORE_CORE_SETTINGS') ?></legend>
				<table class="admintable">
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND_TIP'); ?>">
								<label for="show_emailfriend"><?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND') ?>
							</label> </span>
						</td>
						<td>
						<?php echo VmHTML::checkbox('show_emailfriend', $this->config->get('show_emailfriend')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_PRINTICON_TIP'); ?>">
								<label for="show_printicon"><?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_PRINTICON') ?>
							</label> </span>
						</td>
						<td>
						<?php echo VmHTML::checkbox('show_printicon', $this->config->get('show_printicon')); ?>
						</td>
					</tr>

					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_PDF_ICON_SHOW_EXPLAIN'); ?>">
								<label for="pdf_icon"><?php echo JText::_('COM_VIRTUEMART_PDF_ICON_SHOW') ?>
							</label> </span>
						</td>
						<td>
						<?php echo VmHTML::checkbox('pdf_icon', $this->config->get('pdf_icon')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_SHOW_EXPLAIN'); ?>">
								<label for="ask_question"><?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_SHOW') ?>
							</label> </span>
						</td>
						<td>
						<?php echo VmHTML::checkbox('ask_question', $this->config->get('ask_question')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MIN_LENGTH_EXPLAIN'); ?>">
								<label for="ask_question"><?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MIN_LENGTH') ?>
							</label> </span>
						</td>
						<td>
							<input type="text" value="<?php echo $this->config->get('asks_minimum_comment_length',50); ?>" class="inputbox" size="4" name="asks_minimum_comment_length">
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MAX_LENGTH_EXPLAIN'); ?>">
								<label for="ask_question"><?php echo JText::_('COM_VIRTUEMART_ASK_QUESTION_MAX_LENGTH') ?>
							</label> </span>
						</td>
						<td>
							<input type="text" value="<?php echo $this->config->get('asks_maximum_comment_length',2000); ?>" class="inputbox" size="4" name="asks_maximum_comment_length">
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_PRODUCT_NAVIGATION_SHOW_EXPLAIN'); ?>">
								<label for="product_navigation"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_NAVIGATION_SHOW') ?>
							</label> </span>
						</td>
						<td>
						<?php echo VmHTML::checkbox('product_navigation', $this->config->get('product_navigation')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_RECCOMEND_UNATUH_EXPLAIN'); ?>">
								<label for="ask_question"><?php echo JText::_('COM_VIRTUEMART_RECCOMEND_UNATUH') ?>
							</label> </span>
						</td>
						<td>
						<?php echo VmHTML::checkbox('recommend_unauth', $this->config->get('recommend_unauth')); ?>
						</td>
					</tr>
					<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_LIST_LIMIT_EXPLAIN'); ?>">
				<label for="list_limit"><?php echo JText::_('COM_VIRTUEMART_LIST_LIMIT') ?></label>
				</span>
			</td>
			<td>
				<input type="text" value="<?php echo $this->config->get('list_limit',10); ?>" class="inputbox" size="4" name="list_limit">
			</td>
			</tr>
<?php	/*		<tr>
	    	<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS_EXPLAIN'); ?>">
				<label for="show_products_out_of_stock"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS') ?></label>
				</span>
	    	</td>
	    	<td valign="top">
				<?php echo VmHTML::checkbox('show_products_out_of_stock', $this->config->get('show_products_out_of_stock')); ?>
	    	</td>
			</tr>
			<tr> */?>
			<tr>
	    	<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_DISPLAY_STOCK_EXPLAIN'); ?>">
				<label for="display_stock"><?php echo JText::_('COM_VIRTUEMART_DISPLAY_STOCK') ?></label>
				</span>
	   	 	</td>
	    	<td>
				<?php echo VmHTML::checkbox('display_stock', $this->config->get('display_stock')); ?>
	    	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPONS_ENABLE_EXPLAIN'); ?>">
				<label for="coupons_enable"><?php echo JText::_('COM_VIRTUEMART_COUPONS_ENABLE') ?></label>
				</span>
	   	 	</td>
	    	<td>
				<?php echo VmHTML::checkbox('coupons_enable', $this->config->get('coupons_enable')); ?>
	    	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPONS_EXPIRE_EXPLAIN'); ?>">
				<label for="coupons_default_expire"><?php echo JText::_('COM_VIRTUEMART_COUPONS_EXPIRE') ?></label>
				</span>
	    	</td>
			<td>
				<?php
					// TODO This must go to the view.html.php.... but then... that goes for most of the config sruff I'ld say :-S
					$_defaultExpTime = array(
						 '1,D' => '1 '.JText::_('COM_VIRTUEMART_DAY')
						,'1,W' => '1 '.JText::_('COM_VIRTUEMART_WEEK')
						,'2,W' => '2 '.JText::_('COM_VIRTUEMART_WEEK_S')
						,'1,M' => '1 '.JText::_('COM_VIRTUEMART_MONTH')
						,'3,M' => '3 '.JText::_('COM_VIRTUEMART_MONTH_S')
						,'6,M' => '6 '.JText::_('COM_VIRTUEMART_MONTH_S')
						,'1,Y' => '1 '.JText::_('COM_VIRTUEMART_YEAR')
					);
echo VmHTML::selectList('coupons_default_expire',$this->config->get('coupons_default_expire'),$_defaultExpTime)
				?>
			</td>
</tr>
<tr>
	    	<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_WEIGHT_UNIT_DEFAULT_EXPLAIN'); ?>">
				<label for="weight_unit_default"><?php echo JText::_('COM_VIRTUEMART_WEIGHT_UNIT_DEFAULT') ?></label>
				</span>
	    	</td>
			<td>
				<?php
echo ShopFunctions::renderWeightUnitList('weight_unit_default', $this->config->get('weight_unit_default') );
				?>
			</td>
			</tr>

			<tr>
	    	<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_LWH_UNIT_DEFAULT_EXPLAIN'); ?>">
				<label for="weight_unit_default"><?php echo JText::_('COM_VIRTUEMART_LWH_UNIT_DEFAULT') ?></label>
				</span>
	    	</td>
			<td>
				<?php
echo ShopFunctions::renderLWHUnitList('lwh_unit_default', $this->config->get('lwh_unit_default') );
				?>
			</td>
			</tr>

		</table>
	</fieldset>

<td>
			<fieldset class="checkboxes">
				<legend><span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_POOS_ENABLE_EXPLAIN'); ?>">
					<?php echo JText::_('COM_VIRTUEMART_CFG_POOS_ENABLE') ?></legend></span>
				<div>
					<?php echo VmHTML::checkbox('lstockmail', $this->config->get('lstockmail')); ?>
					     <span class="hasTip"
						title="<?php echo JText::_('COM_VIRTUEMART_CFG_LOWSTOCK_NOTIFY_TIP'); ?>">
								<label for="reviews_autopublish"><?php echo JText::_('COM_VIRTUEMART_CFG_LOWSTOCK_NOTIFY') ?>
								</label> </span>

				</div>
				<?php

				$options = array(
				'none'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_NONE'),
				'disableit'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_IT'),
				'disableit_children'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_IT_CHILDREN'),
				'disableadd'	=>	JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_DISABLE_ADD'),
				'risetime'	=> JText::_('COM_VIRTUEMART_ADMIN_CFG_POOS_RISE_AVATIME')
			);

				echo VmHTML::radioList('stockhandle', $this->config->get('stockhandle','none'),$options);
				?>
				<div style="font-weight:bold;">
					     <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_AVAILABILITY_EXPLAIN'); ?>">
							<?php echo JText::_('COM_VIRTUEMART_AVAILABILITY') ?>
						    </span>
				</div>

				<input type="text" class="inputbox" id="product_availability" name="rised_availability" value="<?php echo $this->config->get('rised_availability'); ?>" />
				<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>'.JText::_('COM_VIRTUEMART_AVAILABILITY').'</b><br/ >'.JText::_('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP1') ?>"></span>

				<?php echo JHTML::_('list.images', 'image', $this->config->get('rised_availability'), " ", $this->imagePath); ?>
				<span class="icon-nofloat vmicon vmicon-16-info tooltip" title="<?php echo '<b>'.JText::_('COM_VIRTUEMART_AVAILABILITY').'</b><br/ >'.JText::sprintf('COM_VIRTUEMART_PRODUCT_FORM_AVAILABILITY_TOOLTIP2',  $this->imagePath ) ?>"></span>

				<img border="0" id="imagelib" alt="<?php echo JText::_('COM_VIRTUEMART_PREVIEW'); ?>" name="imagelib" src="<?php if ($this->config->get('rised_availability')) echo JURI::root(true).$this->imagePath.$this->config->get('rised_availability');?>"/>
			</fieldset>
			<fieldset>
				<legend>



				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_TITLE') ?></legend>
				<table class="admintable">
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_REVIEWS_AUTOPUBLISH_TIP'); ?>">
								<label for="reviews_autopublish"><?php echo JText::_('COM_VIRTUEMART_REVIEWS_AUTOPUBLISH') ?>
								</label> </span>
						</td>
						<td>
							<?php echo VmHTML::checkbox('reviews_autopublish', $this->config->get('reviews_autopublish')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH_TIP'); ?>">
								<label for="reviews_minimum_comment_length"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH') ?>
								</label> </span>
						</td>
						<td><input
							type="text"
							size="6"
							id="reviews_minimum_comment_length"
							name="reviews_minimum_comment_length"
							class="inputbox"
							value="<?php echo $this->config->get('reviews_minimum_comment_length'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH_TIP'); ?>">
								<label><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH'); ?>
								</label> </span>
						</td>
						<td><input
							type="text"
							size="6"
							id="reviews_maximum_comment_length"
							name="reviews_maximum_comment_length"
							class="inputbox"
							value="<?php echo $this->config->get('reviews_maximum_comment_length'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_EXPLAIN'); ?>">
								<label><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW') ?>
							</label> </span>
						</td>
						<td><fieldset class="checkboxes">

						<?php
						$showReviewFor = array(	'none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_NONE'),
											'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_REGISTERED'),
											'all' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_ALL')
						); //showReviewFor
						echo VmHTML::radioList('showReviewFor', $this->config->get('showReviewFor',2),$showReviewFor); ?>

							</fieldset></td>
					</tr>

				    <tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_EXPLAIN'); ?>">
								<label><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW') ?>
							</label> </span>
						</td>
						<td><fieldset class="checkboxes">

						<?php
						$showReviewFor = array('none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_NONE'),
				 						'bought' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_BOUGHT_PRODUCT'),
				 						'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_REGISTERED'),
						//	3 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_ALL')
						);
						echo VmHTML::radioList('reviewMode', $this->config->get('reviewMode',2),$showReviewFor); ?>
							</fieldset></td>
					</tr>

					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_EXPLAIN'); ?>">
								<label><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW') ?>
							</label> </span>
						</td>
						<td>

				<fieldset class="checkboxes">


						<?php
						$showReviewFor = array(	'none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_NONE'),
		    								'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_REGISTERED'),
											'all' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_ALL')
						);
						echo VmHTML::radioList('showRatingFor', $this->config->get('showRatingFor',2),$showReviewFor); ?>

							</fieldset></td>
					</tr>
					<tr>
						<td class="key"><span
							class="hasTip"
							title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_EXPLAIN'); ?>">
								<label><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING') ?>
							</label> </span>
						</td>
						<td>

				<fieldset class="checkboxes">

						<?php
						$showReviewFor = array('none' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_NONE'),
				 						'bought' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_BOUGHT_PRODUCT'),
										'registered' => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_REGISTERED'),
						//	3 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_ALL')	//TODO write system for all users (cookies)
						);
						echo VmHTML::radioList('ratingMode', $this->config->get('ratingMode',2),$showReviewFor); ?>
							</fieldset></td>
					</tr>

				</table>
			</fieldset>
		</td>

	</tr>
</table>
<script type="text/javascript">
	jQuery('#image').change( function() {
		var $newimage = jQuery(this).val();
		jQuery('#product_availability').val($newimage);
		jQuery('#imagelib').attr({ src:'<?php echo JURI::root(true).$this->imagePath ?>'+$newimage, alt:$newimage });
		})
</script>