<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Order_Helper extends Mage_Selenium_TestCase
{

    /**
     * Generates array of strings for filling customer's billing/shipping form
     * @param string $charsType :alnum:, :alpha:, :digit:, :lower:, :upper:, :punct:
     * @param string $addrType Gets two values: 'billing' and 'shipping'.
     *                         Default is 'billing'
     * @param int $symNum min = 5, default value = 32
     * @return array
     * @uses DataGenerator::generate()
     * @see DataGenerator::generate()
     */
    public function customerAddressGenerator($charsType, $addrType = 'billing', $symNum = 32, $required = FALSE)
    {
        $type = array(':alnum:', ':alpha:', ':digit:', ':lower:', ':upper:', ':punct:');
        if (!in_array($charsType, $type) || ($addrType != 'billing' && $addrType != 'shipping')
            || $symNum < 5 || !is_int($symNum)) {
            throw new Exception('Incorrect parameters');
        }
        $return = array();
        $page = $this->getUimapPage('admin', 'create_order_for_existing_customer');
        $fieldset = $page->findFieldset('order_' . $addrType . '_address');
        $fields = $fieldset->getAllFields();
        $requiredFields = $fieldset->getAllRequired();
        $req = array();
        foreach ($requiredFields as $key => $value) {
            $req[] = $value;
        }
        if ($required) {
            foreach ($fields as $fieldsKey => $xpath) {
                if (in_array($fieldsKey, $req)) {
                    $return[$fieldsKey] = $this->generate('string', $symNum, $charsType);
                } else {
                    $return[$fieldsKey] = '%noValue%';
                }
            }
        } else {
            foreach ($fields as $fieldsKey => $xpath) {
                $return[$fieldsKey] = $this->generate('string', $symNum, $charsType);
            }
        }
        $return[$addrType . '_country'] = 'Ukraine';
        $return['address_choice'] = 'new';
        return $return;
    }

    /**
     * Creates order
     * @param array|string $orderData        Array or string with name of dataset to load
     * @param bool         $validate         If $validate == TRUE 'Submit Order' button will not be pressed
     *
     * @return bool|string
     */
    public function createOrder($orderData, $validate = TRUE)
    {
        $orderData = $this->arrayEmptyClear($orderData);
        $storeView = (isset($orderData['store_view'])) ? $orderData['store_view'] : NULL;
        $customer = (isset($orderData['customer_data'])) ? $orderData['customer_data'] : NULL;
        $account = (isset($orderData['account_data'])) ? $orderData['account_data'] : array();
        $products = (isset($orderData['products_to_add'])) ? $orderData['products_to_add'] : array();
        $coupons = (isset($orderData['coupons'])) ? $orderData['coupons'] : NULL;
        $billingAddr = (isset($orderData['billing_addr_data'])) ? $orderData['billing_addr_data'] : NULL;
        $shippingAddr = (isset($orderData['shipping_addr_data'])) ? $orderData['shipping_addr_data'] : NULL;
        $paymentMethod = (isset($orderData['payment_data'])) ? $orderData['payment_data'] : NULL;
        $shippingMethod = (isset($orderData['shipping_data'])) ? $orderData['shipping_data'] : NULL;
        $giftMessages = (isset($orderData['gift_messages'])) ? $orderData['gift_messages'] : array();
        $verProduct = (isset($orderData['prod_verification'])) ? $orderData['prod_verification'] : NULL;
        $verPrTotal = (isset($orderData['prod_total_verification'])) ? $orderData['prod_total_verification'] : NULL;
        $verTotal = (isset($orderData['total_verification'])) ? $orderData['total_verification'] : NULL;

        $this->navigateToCreateOrderPage($customer, $storeView);
        $this->fillForm($account);
        foreach ($products as $value) {
            $this->addProductToOrder($value);
        }
        if ($coupons) {
            $this->applyCoupon($coupons, $validate);
        }
        if ($billingAddr) {
            $billingChoise = $billingAddr['address_choice'];
            $this->fillOrderAddress($billingAddr, $billingChoise, 'billing');
        }
        if ($shippingAddr) {
            $shippingChoise = $shippingAddr['address_choice'];
            $this->fillOrderAddress($shippingAddr, $shippingChoise, 'shipping');
        }
        if ($shippingMethod) {
            $this->clickControl('link', 'get_shipping_methods_and_rates', FALSE);
            $this->pleaseWait();
            $this->selectShippingMethod($shippingMethod, $validate);
        }
        $this->selectPaymentMethod($paymentMethod, $validate);
        $this->addGiftMessage($giftMessages);
        if ($verProduct && $verTotal) {
            $this->shoppingCartHelper()->verifyPricesDataOnPage($verProduct, $verTotal);
        }
        if ($verPrTotal) {
            $this->verifyProductsTotal($verPrTotal);
        }
        $this->submitOreder();
    }

    /**
     *
     */
    public function submitOreder()
    {
        $this->saveForm('submit_order', false);
        $this->defineOrderId();
        $this->validatePage();
    }

    /**
     * Fills customer's addresses at the order page.
     *
     * @param string $addressType   'new', 'exist', 'sameAsBilling'
     * @param string $address       'billing' or 'shipping'
     * @param array  $addressData
     */
    public function fillOrderAddress($addressData, $addressChoise = 'new', $addressType = 'billing')
    {
        if (is_string($addressData)) {
            $addressData = $this->loadData($addressData);
        }

        if ($addressChoise == 'sameAsBilling') {
            $this->fillForm(array('shipping_same_as_billing_address' => 'yes'));
        }
        if ($addressChoise == 'new') {
            $xpath = $this->_getControlXpath('dropdown', $addressType . '_address_choice');
            if ($this->isElementPresent($xpath . "/option[@selected]")) {
                $this->select($xpath, 'label=Add New Address');
                if ($addressType == 'shipping') {
                    $this->pleaseWait();
                }
            }
            if ($addressType == 'shipping') {
                $xpath = $this->_getControlXpath('checkbox', 'shipping_same_as_billing_address');
                $value = $this->getValue($xpath);
                if ($value == 'on') {
                    $this->click($xpath);
                    $this->pleaseWait();
                }
            }
            $this->fillForm($addressData);
        }
        if ($addressChoise == 'exist') {
            if ($addressType == 'shipping') {
                $xpath = $this->_getControlXpath('checkbox', 'shipping_same_as_billing_address');
                $value = $this->getValue($xpath);
                if ($value == 'on') {
                    $this->click($xpath);
                    $this->pleaseWait();
                }
            }
            $addressLine = $this->defineAddressToChoose($addressData, $addressType);
            $this->fillForm(array($addressType . '_address_choice' => 'label=' . $addressLine));
        }
    }

    /**
     * Returns address that was found and can be selected from existing customer addresses.
     *
     * @param array         $keyWords
     *
     * @return bool|string               The most suitable address found by using keywords
     */
    public function defineAddressToChoose(array $addressData, $addressType = 'billing')
    {
        $inString = array();
        $needKeys = array('first_name', 'last_name', 'street_address_1', 'street_address_2', 'city', 'zip_code',
                          'country', 'state', 'region');
        foreach ($needKeys as $value) {
            if (array_key_exists($addressType . '_' . $value, $addressData)) {
                $inString[$addressType . '_' . $value] = $addressData[$addressType . '_' . $value];
            }
        }

        if (!$inString) {
            $this->fail('Data to select the address wrong');
        }

        $xpathDropDown = $this->_getControlXpath('dropdown', $addressType . '_address_choice');
        $addressCount = $this->getXpathCount($xpathDropDown . '/option');

        for ($i = 1; $i <= $addressCount; $i++) {
            $res = 0;
            $addressValue = $this->getText($xpathDropDown . "/option[$i]");
            foreach ($inString as $v) {
                $res += preg_match('/' . preg_quote($v) . '/', $addressValue);
            }
            if ($res == count($inString)) {
                $res = $addressValue;
                break;
            }
        }

        if (isset ($res) && is_string($res)) {
            return $res;
        }
        $this->fail('Can not define address');
    }

    /**
     * Defines order id
     *
     * @param string $fieldset
     * @return bool|integer
     */
    public function defineOrderId()
    {
        $xpath = "//*[contains(@class,'head-sales-order')]";
        if ($this->isElementPresent($xpath)) {
            $text = $this->getText($xpath);
            $orderId = trim(substr($text, strpos($text, "#") + 1, -(strpos(strrev($text), "|") + 1)));
            $this->addParameter('order_id', '#' . $orderId);
            return $orderId;
        }
        return 0;
    }

    /**
     * Orders product during forming order
     *
     * @param array $productData Product in array to add to order. Function should be called for each product to add
     */
    public function addProductToOrder(array $productData)
    {
        $configur = array();
        $aditionalData = array();
        foreach ($productData as $key => $value) {
            if (!preg_match('/^filter_/', $key)) {
                $aditionalData[$key] = $value;
                unset($productData[$key]);
            }
            if ($key == 'qty_to_add') {
                $aditionalData['product_qty'] = $value;
                unset($productData[$key]);
            }
            if ($key == 'filter_sku' || $key == 'filter_name') {
                $productSku = $value;
            }
            if ($key == 'configurable_options') {
                $configur = $value;
            }
        }

        if ($productData) {
            $this->clickButton('add_products', FALSE);
            $xpathProduct = $this->search($productData);
            $this->assertNotEquals(NULL, $xpathProduct, 'Product is not found');
            $this->addParameter('productXpath', $xpathProduct);
            $configurable = FALSE;
            $configureLink = $this->_getControlXpath('link', 'configure');
            if (!$this->isElementPresent($configureLink . '[@disabled]')) {
                $configurable = TRUE;
            }
            $this->click($xpathProduct . "//input[@type='checkbox']");
            if ($configurable && $configur) {
                $this->_parseMessages();
                $before = $this->messages;
                $this->pleaseWait();
                $this->configureProduct($configur);
                $this->clickButton('ok', FALSE);
                $this->_parseMessages();
                $after = $this->messages;
                $result = array();
                foreach ($after as $key => $value) {
                    if ($key == 'success') {
                        continue;
                    }
                    if (is_array($value) && (array_key_exists($key, $before) && is_array($before[$key]))) {
                        $result = array_merge($result, array_diff($value, $before[$key]));
                    }
                }
                if ($result) {
                    $this->fail("Error(s) when configure product '$productSku':\n" .
                                implode("\n", $result));
                }
            }
            $this->clickButton('add_selected_products_to_order', FALSE);
            $this->pleaseWait();
            if ($aditionalData) {
                $this->reconfigProduct($productSku, $aditionalData);
            }
        }
    }

    /**
     * Configuring product when placing to order.
     *
     * @param array $productData Product in array to add to order. Function should be called for each product to add
     */
    public function configureProduct(array $configurData)
    {
        $set = $this->getCurrentUimapPage()->findFieldset('product_composite_configure_form');

        foreach ($configurData as $key => $value) {
            if (is_array($value)) {
                $optionTitle = (isset($value['title'])) ? $value['title'] : '';
                $this->addParameter('optionTitle', $optionTitle);
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        $type = (isset($v['fieldType'])) ? $v['fieldType'] : '';
                        $parameter = (isset($v['fieldParameter'])) ? $v['fieldParameter'] : '';
                        $field_value = (isset($v['fieldsValue'])) ? $v['fieldsValue'] : '';
                        $this->addParameter('optionParameter', $parameter);
                        $method = 'getAll' . ucfirst(strtolower($type));
                        if ($method == 'getAllCheckbox') {
                            $method .= 'es';
                        } else {
                            $method .= 's';
                        }
                        $a = $set->$method();
                        foreach ($a as $field => $fieldValue) {
                            if ($this->isElementPresent($fieldValue)) {
                                $this->fillForm(array($field => $field_value));
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * The way customer will pay for the order
     *
     * @param array|string $paymentMethod
     */
    public function selectPaymentMethod($paymentMethod, $validate = TRUE)
    {
        if (is_string($paymentMethod)) {
            $paymentMethod = $this->loadData($paymentMethod);
        }
        $payment = (isset($paymentMethod['payment_method'])) ? $paymentMethod['payment_method'] : NULL;
        $card = (isset($paymentMethod['payment_info'])) ? $paymentMethod['payment_info'] : NULL;

        if ($payment) {
            if ($this->errorMessage('no_payment')) {
                if ($validate) {
                    $this->fail('No Payment Information Required');
                }
            } else {
                $this->addParameter('paymentTitle', $payment);
                $xpath = $this->_getControlXpath('radiobutton', 'check_payment_method');
                $this->click($xpath);
                $this->pleaseWait();
                if ($card) {
                    $paymentId = $this->getAttribute($xpath . '/@value');
                    $this->addParameter('paymentId', $paymentId);
                    $this->fillForm($card);
                    $this->validate3dSecure();
                }
            }
        }
    }

    /**
     *
     */
    public function validate3dSecure($password = '1234')
    {
        $xpath = $this->_getControlXpath('fieldset', '3d_secure_card_validation');
        if ($this->isElementPresent($xpath)) {
            $this->clickButton('start_reset_validation', FALSE);
            $this->pleaseWait();
            $alert = $this->isAlertPresent();
            if ($alert) {
                $text = $this->getAlert();
                $this->fail($text);
            }
            $this->waitForElement("//div//iframe[@id='centinel_authenticate_iframe'" .
                                  " and normalize-space(@style)='display: block;']");
            $this->waitForElement("//body[@onbeforeunload and @onload]");
            if ($this->waitForElement("//input[@name='external.field.password']", 5)) {
                $this->type("//input[@name='external.field.password']", $password);
                $this->click("//input[@value='Submit']");
                $a = "//font//b[text()='Incorrect, Please try again']";
                $b = "//html/body/h1[text()='Verification Successful']";
                $this->waitForElement(array($a, $b));
                $this->assertElementPresent("//html/body/h1[text()='Verification Successful']");
            } else {
                $this->fail('3D Secure frame is not loaded(maybe wrong card)');
            }
        }
    }

    /**
     * The way to ship the order
     *
     * @param array|string $shippingMethod
     */
    public function selectShippingMethod($shippingMethod, $validate = TRUE)
    {
        if (is_string($shippingMethod)) {
            $shippingMethod = $this->loadData($shippingMethod);
        }
        $this->messages['error'] = array();
        $shipService = (isset($shippingMethod['shipping_service'])) ? $shippingMethod['shipping_service'] : NULL;
        $shipMethod = (isset($shippingMethod['shipping_method'])) ? $shippingMethod['shipping_method'] : NULL;
        if (!$shipService or !$shipMethod) {
            $this->messages['error'][] = 'Shipping Service(or Shipping Method) is not set';
        } else {
            $this->addParameter('shipService', $shipService);
            $this->addParameter('shipMethod', $shipMethod);
            $methodUnavailable = $this->_getControlXpath('message', 'ship_method_unavailable');
            $noShipping = $this->_getControlXpath('message', 'no_shipping');
            if ($this->isElementPresent($methodUnavailable) || $this->isElementPresent($noShipping)) {
                $this->messages['error'][] = 'No Shipping Method is available for this order';
            } elseif ($this->isElementPresent($this->_getControlXpath('field', 'ship_service_name'))) {
                $method = $this->_getControlXpath('radiobutton', 'ship_method');
                if ($this->isElementPresent($method)) {
                    $this->click($method);
                    $this->pleaseWait();
                } else {
                    $this->messages['error'][] = 'Shipping Method "' . $shipMethod . '" for "'
                                                 . $shipService . '" is currently unavailable.';
                }
            } else {
                $this->messages['error'][] = 'Shipping Service "' . $shipService . '" is currently unavailable.';
            }
        }
        if ($this->messages['error'] && $validate) {
            $this->fail(implode("\n", $this->messages['error']));
        }
    }

    /**
     * Gets to 'Create new Order page'
     *
     * @param array|string $customerData    Array with customer data to search or string with dataset to load
     */
    public function navigateToCreateOrderPage($customerData, $storeView)
    {
        $this->clickButton('create_new_order');
        if ($customerData == NULL) {
            $this->clickButton('create_new_customer', FALSE);
            $this->pleaseWait();
        } else {
            if (is_string($customerData)) {
                $customerData = $this->loadData($customerData);
            }
            $this->assertTrue($this->searchAndOpen($customerData, FALSE, 'order_customer_grid'),"Customer isn't found");
        }

        $storeSelectorXpath = $this->_getControlXpath('fieldset', 'order_store_selector');
        // Select a store if there is more then one default store
        if ($this->isElementPresent($storeSelectorXpath .
                                    "[not(contains(@style,'display: none'))][not(contains(@style,'display:none'))]")) {
            if ($storeView) {
                $this->addParameter('storeName', $storeView);
                $this->clickControl('radiobutton', 'choose_main_store', FALSE);
                $this->pleaseWait();
            } else {
                $this->fail('Store View is not set');
            }
        }
    }

    /**
     * Reconfigure already added to order products (change quantity, add discount, etc)
     *
     * @param array $reconfigProduct Array with the products and data to reconfigure
     */
    public function reconfigProduct($productSku, array $productData)
    {
        $this->addParameter('sku', $productSku);
        $this->fillForm($productData);
        $this->clickButton('update_items_and_quantity', FALSE);
        $this->pleaseWait();
    }

    /**
     * Adding gift messaged to products during creating order at the backend.
     *
     * @param array $giftMessages Array with the gift messages for the products
     */
    public function addGiftMessage(array $giftMessages)
    {
        if (array_key_exists('entire_order', $giftMessages)) {
            $this->fillForm($giftMessages['entire_order']);
        }
        if (array_key_exists('individual', $giftMessages)) {
            foreach ($giftMessages['individual'] as $product => $options) {
                if (is_array($options) && isset($options['sku_product'])) {
                    $this->addParameter('sku', $options['sku_product']);
                    $this->clickControl('link', 'gift_options', FALSE);
                    $this->waitForAjax();
                    $this->fillForm($options);
                    $this->clickButton('ok', FALSE);
                    $this->pleaseWait();
                }
            }
        }
    }

    /**
     *
     * @param array $giftMessages
     */
    public function verifyGiftMessage(array $giftMessages)
    {
        if (array_key_exists('entire_order', $giftMessages)) {
            $this->verifyForm($giftMessages['entire_order']);
        }
        if (array_key_exists('individual', $giftMessages)) {
            foreach ($giftMessages['individual'] as $product => $options) {
                if (is_array($options) && isset($options['sku_product'])) {
                    $this->addParameter('sku', $options['sku_product']);
                    $this->clickControl('link', 'gift_options', FALSE);
                    $this->waitForAjax();
                    $this->verifyForm($options);
                    $this->clickButton('ok', FALSE);
                    $this->pleaseWait();
                }
            }
        }
        if (!empty($this->messages['error'])) {
            $this->fail(implode("\n", $this->messages['error']));
        }
    }

    /**
     * Applying coupon for the products in order
     *
     * @param array $couponCode
     */
    public function applyCoupon($coupons, $validate = TRUE)
    {
        if (is_string($coupons)) {
            $coup[] = $coupons;
            $coupons = $coup;
        }
        $xpath = $this->_getControlXpath('fieldset', 'order_apply_coupon_code');
        if (!$this->isElementPresent($xpath) && $coupons) {
            $this->fail('Can not add coupon(Product is not added)');
        }

        foreach ($coupons as $code) {
            $this->fillForm(array('coupon_code' => $code));
            $this->clickButton('apply', FALSE);
            $this->pleaseWait();
            if ($validate) {
                $this->addParameter('couponCode', $code);
                $this->assertTrue($this->successMessage('success_applying_coupon'), $this->messages);
                $this->assertFalse($this->errorMessage('invalid_coupon_code'), $this->messages);
            }
        }
    }

    /**
     * Verifies the prices in product total grid
     *
     * @param array $verificationData
     */
    public function verifyProductsTotal(array $verificationData)
    {
        $actualData = array();

        $needColumnNames = array('Product', 'Subtotal', 'Discount', 'Row Subtotal');
        $names = $this->getTableHeadRowNames("//*[@id='order-items_grid']/table");
        $xpath = $this->_getControlXpath('pageelement', 'product_table_tfoot');
        foreach ($needColumnNames as $value) {
            $number = array_search($value, $names);
            if ($value == 'Product') {
                $number += 1;
            }
            $key = trim(strtolower(preg_replace('#[^0-9a-z]+#i', '_', $value)), '_');
            $actualData[$key] = $this->getText($xpath . "//td[$number]");
        }
        $this->messages = $this->shoppingCartHelper()->compareArrays($actualData, $verificationData, 'Total');

        if (!empty($this->messages['error'])) {
            $this->fail(implode("\n", $this->messages['error']));
        }
    }


    /**
     * Compare arrays
     *
     * @param $httpHelperPath
     * @param $logFileName
     * @param $inputArray
     * @return bool|array
     */
    public function compareArrays($httpHelperPath,$logFileName,$inputArray)
    {
        $subject = $this->getLastRecord($httpHelperPath,$logFileName);
        $responseParams = $this->getResponse($subject);
        $resultArray = array_diff($inputArray,$responseParams);
        return (empty($resultArray)) ? TRUE : $resultArray;
    }

    /**
     * Define correct array for compare
     *
     * @param $subject
     * @return array
     */
    protected function getParamsArray($subject)
    {
        preg_match_all('/\[(.*)\] => (.*)/',$subject,$arr);

        $result = array();
        foreach ($arr[1] as $key => $value) {
            if (!empty($value)) {
                $result[$value] = $arr[2][$key];
            }
        }
        return   $result;
    }

    /**
     * Define request array
     *
     * @param $subject
     * @return array
     */
    protected function getRequest($subject)
    {
        $requestSubject = substr($subject,strpos($subject,'[request]'),strpos($subject,")\n")-strpos($subject,'[request]')+1);
        $requestSubject = substr($requestSubject,strpos($requestSubject,"(\n"),strpos($requestSubject,")"));
        return $this->getParamsArray($requestSubject);
    }

    /**
     * Define response array
     *
     * @param $subject
     * @return array
     */
    protected function getResponse($subject)
    {
        $responseSubject = substr($subject,strpos($subject,'[response]'),strpos($subject,")\n")-strpos($subject,'[request]')+1);
        $responseSubject = substr($responseSubject,strpos($responseSubject,"(\n"),
                                  strpos($responseSubject,")")-strpos($responseSubject,"(\n"));
        return $this->getParamsArray($responseSubject);
    }
    /**
     * Find last record into Log File
     *
     * @param $httpHelperPath
     * @param $logFileName
     * @return string
     */
    protected function getLastRecord($httpHelperPath,$logFileName)
    {
        $arrayResult =  file_get_contents($httpHelperPath . '?log_file_name=' . $logFileName);
        $pathVerification = strcmp(trim($arrayResult),'Could not open File');
        if ($pathVerification == 0){
            $this->fail("Log file could not be opened");
        }
        return $arrayResult;
    }

}
