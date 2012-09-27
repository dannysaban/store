<?php

/**
 *  ICEPAY Basic module for VirtueMart
 *  Display the postback URL in admin config
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
class JElementIcepayUrl extends JElement {

    var $_name = 'OrderStates';

    function fetchElement($name, $value, &$node, $control_name) {

        $cid = JRequest::getVar('cid');
        $current = $cid[0];

        return JROUTE::_(JURI::root() . 'index.php?option=com_virtuemart&view=icepayresponse&task=notify&pm='.$current.'&');

    }

}