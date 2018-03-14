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
class Flutterwave_Rave_Block_Form_Rave extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('rave/form/flutterwave_rave.phtml');
  }
}
