<?php


/**
 *  ICEPAY Basic module for VirtueMart
 *  Retrieve the paymentmethods from the API
 *
 *  @version 1.0.0
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2012 ICEPAY B.V.
 *  www.icepay.com
 *
 */


/*
 * This class is used by VirtueMart Payment or Shipment Plugins
 * which uses JParameter
 * So It should be an extension of JElement
 * Those plugins cannot be configured througth the Plugin Manager anyway.
 */
class JElementIcepayPaymentmethod extends JElement {

    function fetchElement($name, $value, &$node, $control_name) {

        $paymentmethods = Icepay_Api_Basic::getInstance()->readFolder()->getArray();

        $options = array ();
        $options[] = JHTML::_('select.option', "basicmode", JText::_("BasicMode"));

        // Mutliple paymentmethods disabled in this version, some paymentmethod require an issuer selection
        /*
        foreach ($paymentmethods as $pmname => $class)
        {
                $pm = new $class();
                $text	= $pm->getReadableName();
                $options[] = JHTML::_('select.option', $pmname, JText::_($text));
        }
         */

        $class = '';

        return JHTML::_('select.genericlist', $options, $control_name . '[' . $name . ']', $class, 'value', 'text', $value, $control_name . $name);
    }

}