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

class Flutterwave_Rave_PaymentController extends Mage_Core_Controller_Front_Action
{
    public function cancelAction()
    {
        Mage::getSingleton('core/session')->addError(
            Mage::helper('flutterwave_rave')->__("Payment cancelled."));

        $session = Mage::getSingleton('checkout/session');
        if ($session->getLastRealOrderId())
        {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId())
            {
                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED)
                {
                    $order->registerCancellation("Canceled by User")->save();
                }
                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                //Return quote
                if ($quote->getId())
                {
                    $quote->setIsActive(1)
                        ->setReservedOrderId(NULL)
                        ->save();
                    $session->replaceQuote($quote);
                }

                //Unset data
                $session->unsLastRealOrderId();
            }
        }

        return $this->getResponse()->setRedirect( Mage::getUrl('checkout/onepage'));
    }

    public function popAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'flutterwave_rave',array('template' => 'rave/inline.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function redirectAction()
    {
        $transaction = Mage::helper('flutterwave_rave')->initializeTransaction();
        if(!$transaction){
            return;
        }
        if($transaction->error)
        {
            Mage::getModel('adminnotification/inbox')->addMajor(
                Mage::helper('flutterwave_rave')->__("Error while attempting to initialize transaction for order: " . $transaction->orderId),
                Mage::helper('flutterwave_rave')->__($transaction->error),
                '',
                true
            );
        }
        else
        {
            $this->getResponse()->setRedirect($transaction->authorization_url);
        }
    }

    public function responseAction()
    {
        $success = false;

        $orderId = $this->getRequest()->get("orderid");
        $reference = $this->getRequest()->get("txref");

        // Both are required
        if(!$orderId || !$reference){
            return;
        }

        // reference must start with orderId by design
        if(strpos($reference, $orderId) !== 0){
            return;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if(!$order){
            return;
        }


        // verify transaction with rave
        $transactionStatus = Mage::helper('flutterwave_rave')->verifyTransaction($reference);        
        
        if(!empty($transactionStatus->error))
        {
            if (!empty($_GET['cancel'])) {
                return $this->cancelAction();
            }

            Mage::getModel('adminnotification/inbox')->addMajor(
                Mage::helper('flutterwave_rave')->__("Error while attempting to verify transaction: reference: " . $reference),
                Mage::helper('flutterwave_rave')->__($transactionStatus->error),
                '',
                true
            );
        }
        elseif($transactionStatus->status == 'successful')
        {
            $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
            $order->save();
            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success');
            $order->sendNewOrderEmail();
            $success = true;
        }
        else
        {
            $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, $transactionStatus->status);
            $order->save();
            $order->sendNewOrderEmail();

            Mage::getSingleton('checkout/session')->unsQuoteId();
        }


        if(!$success){
            Mage::getSingleton('core/session')->addError(
                Mage::helper('flutterwave_rave')->__($transactionStatus->error));
            Mage_Core_Controller_Varien_Action::_redirect('checkout/cart');
        }

    }
}
