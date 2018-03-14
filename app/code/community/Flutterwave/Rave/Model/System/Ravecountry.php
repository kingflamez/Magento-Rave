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
 * Used in creating options for Rave Country config value selection
 *
 */
class Flutterwave_Rave_Model_System_Ravecountry
{

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value' => 'NG', 'label' => Mage::helper('adminhtml')->__('Nigeria')),
      array('value' => 'KE', 'label' => Mage::helper('adminhtml')->__('Kenya')),
      array('value' => 'GH', 'label' => Mage::helper('adminhtml')->__('Ghana')),
      array('value' => 'ZA', 'label' => Mage::helper('adminhtml')->__('South Africa')),
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
      'NG' => Mage::helper('adminhtml')->__('Nigeria'),
      'KE' => Mage::helper('adminhtml')->__('Kenya'),
      'GH' => Mage::helper('adminhtml')->__('Ghana'),
      'ZA' => Mage::helper('adminhtml')->__('South Africa'),
    );
  }

}
