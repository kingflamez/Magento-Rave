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
class Flutterwave_Rave_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TEST_MODE = 'payment/flutterwave_rave/test_mode';

    const XML_PATH_LIVE_PUBLIC_KEY = 'payment/flutterwave_rave/live_public_key';
    const XML_PATH_LIVE_SECRET_KEY = 'payment/flutterwave_rave/live_secret_key';

    const XML_PATH_TEST_PUBLIC_KEY = 'payment/flutterwave_rave/test_public_key';
    const XML_PATH_TEST_SECRET_KEY = 'payment/flutterwave_rave/test_secret_key';

    const XML_PATH_LOGO = 'payment/flutterwave_rave/logo';
    const XML_PATH_BUTTON_TEXT = 'payment/flutterwave_rave/button_text';
    const XML_PATH_COUNTRY = 'payment/flutterwave_rave/country';
    const XML_PATH_PAYMENT_METHOD = 'payment/flutterwave_rave/payment_method';
    const XML_PATH_ORDER_STATUS = 'payment/flutterwave_rave/order_status';

    public function getSecretKey()
    {
        if (Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_TEST_MODE)) {
            return Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_TEST_SECRET_KEY);
        } else {
            return Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_LIVE_SECRET_KEY);
        }

    }

    public function getPublicKey()
    {
        if (Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_TEST_MODE)) {
            return Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_TEST_PUBLIC_KEY);
        } else {
            return Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_LIVE_PUBLIC_KEY);
        }

    }

    public function getOrderID()
    {
        return Mage::getSingleton('checkout/session')->getLastRealOrderId();
    }

    function generateReference($orderId)
    {
        $charid = "";
        $max = mb_strlen('0123456789abcdef', '8bit') - 1;
        for ($i = 0; $i < 50; ++$i) {
            $charid .= '0123456789abcdef'[rand(0, $max)];
        }
        $hyphen = chr(45);// "-"
        $reference = $orderId . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . '5' . substr($charid, 11, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);// "}"
        return $reference;
    }

    function verifyTransaction($reference)
    {
        return $this->requery($reference, 0);
    }

    function requery($reference, $requeryCount)
    {
        $transactionStatus = new stdClass();
        $txref = $reference;
        $requeryCount++;
        $data = array(
            'txref' => $txref,
            'SECKEY' => $this->getSecretKey(),
            'last_attempt' => '1'
        // 'only_successful' => '1'
        );

        
        if (Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_TEST_MODE)) {
            $apiLink = "http://flw-pms-dev.eu-west-1.elasticbeanstalk.com/";
        } else {
            $apiLink = "https://api.ravepay.co/";
        }

        // make request to endpoint.
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiLink . 'flwv3-pug/getpaidx/api/xrequery');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);
        $resp = json_decode($response, false);

        if ($resp && $resp->status === "success") {
            if ($resp && $resp->data && $resp->data->status === "successful") {
                $transactionStatus = $resp->data;
            } elseif ($resp && $resp->data && $resp->data->status === "failed") {
                // rave has an error message for us
                $transactionStatus->error = "Error: " . $resp->data->chargemessage;
            } else {
                // I will requery again here. Just incase we have some devs that cannot setup a queue for requery. I don't like this.
                if ($requeryCount > 4) {
                    $transactionStatus->error = "Transaction Failed";
                } else {
                    sleep(3);
                    return $this->requery($reference, $requeryCount);
                }
            }
        } else {
            if ($requeryCount > 4) {
                $transactionStatus->error = "Transaction Failed";
            } else {
                sleep(3);
                return $this->requery($reference, $requeryCount);
            }
        }

        return $transactionStatus;
    }


    function initializeTransaction()
    {
        $stagingUrl = 'https://rave-api-v2.herokuapp.com';
        $liveUrl = 'https://api.ravepay.co';
        $postfields = Mage::helper('flutterwave_rave')->getFormParams();

        if (!$postfields) {
            return;
        }

        ksort($postfields);
        $stringToHash = "";
        foreach ($postfields as $key => $val) {
            $stringToHash .= $val;
        }
        $stringToHash .= $secretKey;
        $hashedValue = hash('sha256', $stringToHash);
        $env = "staging";

        if (Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_TEST_MODE)) {
            $baseUrl = $stagingUrl;
        } else {
            $baseUrl = $liveUrl;
        }
        $transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue));
        $json = json_encode($transactionData);
        $datas = "";
        foreach ($transactionData as $key => $value) {
            $datas .= $key . ": '" . $value . "',";
        }

        $htmlOutput = "<form onsubmit='event.preventDefault(); pay();'>
          <button type='submit' class='btn btn-primary' style='cursor:pointer;' value='" . $payButtonText . "' id='ravepaybutton'>" . $payButtonText . "</button>
        </form>
        <script type='text/javascript' src='" . $baseUrl . "/flwv3-pug/getpaidx/api/flwpbf-inline.js'></script>
        <script>
        function pay() {
        var data = JSON.parse('" . json_encode($transactionData = array_merge($postfields, array('integrity_hash' => $hashedValue))) . "');
        getpaidSetup({" .
            $datas
            . "
        onclose: function() {
          window.location = '" . Mage::getUrl('rave/payment/cancel') . "'
        },
          callback: function(response) {
            var flw_ref = response.tx.flwRef; // collect flwRef returned and pass to a                  server page to complete status check.
            console.log('This is the response returned after a charge', response);
            if (
              response.tx.chargeResponseCode == '00' ||
              response.tx.chargeResponseCode == '0'
            ) {
              window.location = '" . Mage::getUrl('rave/payment/response', array('_query' => array('orderId' => $orderId, 'txref' => $postfields['txref']))) . "';
            } else {
                window . location = '" . Mage::getUrl('rave/payment/response', array('_query' => array('orderId' => $orderId, 'txref' => $postfields['txref']))) . "';
            }
        }
        }
        )
        ;}
        < / script >";

        echo $htmlOutput;
    }

    function getFormParams()
    {
        $order = new Mage_Sales_Model_Order();
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();

            // return blank params if no order is found
        if (!$orderId) {
            return array();
        }
        $order->loadByIncrementId($orderId);

            // get an email for this transaction
        $billing = $order->getBillingAddress();
        if ($order->getBillingAddress()->getEmail()) {
            $email = $order->getBillingAddress()->getEmail();
        } else {
            $email = $order->getCustomerEmail();
        }


        $postfields = array();
        $postfields['PBFPubKey'] = $this->getPublicKey();
        $postfields['customer_email'] = $email;
        $postfields['customer_firstname'] = $billing->getFirstname();
        $postfields['custom_logo'] = Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_LOGO);
        $postfields['pay_button_text'] = Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_BUTTON_TEXT);
        $postfields['customer_lastname'] = $billing->getLastname();
        $postfields['custom_description'] = $this->__('Order ID: ') . $orderId;
        $postfields['customer_phone'] = $billing->getTelephone();
        $postfields['country'] = Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_COUNTRY);
        $postfields['txref'] = $this->generateReference($orderId);
        $postfields['payment_method'] = Mage::getStoreConfig(Flutterwave_Rave_Helper_Data::XML_PATH_PAYMENT_METHOD);
        $postfields['amount'] = $order->getGrandTotal() + 0;
        $postfields['currency'] = $order->getOrderCurrencyCode();

        return $postfields;
    }
}
