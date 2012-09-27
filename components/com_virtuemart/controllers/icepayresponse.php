<?php

/**
 *  ICEPAY Basic module for VirtueMart
 *  Controller for the payment response
 *
 *  @version 1.0.0
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2012 ICEPAY B.V.
 *  www.icepay.com
 *
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

require_once(dirname(__FILE__) . '/pluginresponse.php');

/**
 * Controller for the payment response view
 *
 * @package VirtueMart
 * @subpackage paymentResponse
 * @author Valérie Isaksen
 *
 */
class VirtueMartControllerIcepayresponse extends VirtueMartControllerPluginresponse {

    /**
     * Construct the cart
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
    }

    function notify() {
        if (!class_exists('vmPSPlugin'))
            require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php'); JPluginHelper::importPlugin('vmpayment');

        $return_context = "";
        $dispatcher = JDispatcher::getInstance();
        $html = "";
        $returnValues = $dispatcher->trigger('plgVmOnPaymentResponseReceived', array('html' => &$html));

        //If this script is reached then there was no post data
        echo("The postback URL was installed correctly");
        exit();
    }

    function result() {
        if (!class_exists('vmPSPlugin'))
            require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php'); JPluginHelper::importPlugin('vmpayment');

        $return_context = "";
        $dispatcher = JDispatcher::getInstance();
        $html = "";
        $returnValues = $dispatcher->trigger('plgVmOnPaymentResponseReceived', array('html' => &$html));

        if (JRequest::getVar("icepay_msg")) {

            $view = $this->getView('pluginresponse', 'html');

            if (version_compare(JVERSION, '2.5.0', 'ge')) {
                $view->assignRef('paymentResponse', Jtext::_('COM_VIRTUEMART_CART_THANKYOU'));
                $view->assignRef('paymentResponseHtml', $html, 'post');
            } else {
                JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_CART_THANKYOU'));
                JRequest::setVar('paymentResponseHtml', $html, 'post');
            }
        } else {
            $view = $this->getView('cart', 'html');
        }

        /* Display it all */
        $layoutName = JRequest::getVar('layout', 'default');
        $view->setLayout($layoutName);
        $view->display();
    }

}

//pure php no Tag
