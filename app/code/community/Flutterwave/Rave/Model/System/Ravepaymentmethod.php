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

/**
 * Used in creating options for Rave Payment Method config value selection
 *
 */
class Flutterwave_Rave_Model_System_Ravepaymentmethod
{

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value' => 'both', 'label' => Mage::helper('adminhtml')->__('All')),
      array('value' => 'ussd', 'label' => Mage::helper('adminhtml')->__('USSD Only')),
      array('value' => 'card', 'label' => Mage::helper('adminhtml')->__('Cards Only')),
      array('value' => 'account', 'label' => Mage::helper('adminhtml')->__('Account Only')),
    );
  }

  /**
   * Get options in "key-value" format
   *
   * @return array
   */
  public function toArray()
  {
    return array(
      'both' => Mage::helper('adminhtml')->__('All'),
      'ussd' => Mage::helper('adminhtml')->__('USSD Only'),
      'card' => Mage::helper('adminhtml')->__('Cards Only'),
      'account' => Mage::helper('adminhtml')->__('Account Only'),
    );
  }

}
