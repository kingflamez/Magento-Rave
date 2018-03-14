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
class Flutterwave_Rave_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
    protected $_code  = 'flutterwave_rave';
    protected $_formBlockType = 'flutterwave_rave/form_rave';
    protected $_infoBlockType = 'flutterwave_rave/info_rave';

    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        return $this;
    }

    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();

        return $this;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('rave/payment/pop');
    }
}
