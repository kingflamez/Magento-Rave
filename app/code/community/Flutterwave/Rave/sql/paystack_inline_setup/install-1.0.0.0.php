<?php
/**
 * Flutterwave Rave Extension
 *
 * DISCLAIMER
 * This file will not be supported if it is modified.
 *
 * @category   Flutterwave
 * @author     Oluwole Adebiyi (@kingflamez)
 * @package    Flutterwave_Rave
 * @copyright  Copyright (c) 2018 Oluwole Adebiyi. (https://github.com/kingflamez)
 * @license    https://raw.githubusercontent.com/kingflamez/rave-magento/master/LICENSE   MIT License (MIT)
 */

$installer = $this;

$helper = Mage::helper('flutterwave_rave');

$text = 'Flutterwave Rave Extension has been successfully installed and is ready to be configured.';

Mage::getModel('adminnotification/inbox')->addMajor(
    $helper->__($text),
    $helper->__($text),
    '',
    true
);
