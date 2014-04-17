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
 * Creting Order with promoted product
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Order_Create_WithProductWithWarningTest extends Mage_Selenium_TestCase
{

    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Order creation with product that contains validation message</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose Store.</p>
     * <p>5.Press 'Add Products' button.</p>
     * <p>6.Add product</p>
     * <p>7.Fill in billing and shipping addresses.</p>
     * <p>8.Choose shipping method.</p>
     * <p>9.Choose payment method.</p>
     * <p>10.Submit order.</p>
     * <p>Expected result:</p>
     * <p>Warning message appears before submitting order. Order is created</p>
     *
     * @dataProvider dataProductWithValidationMessage
     * @test
     */
    public function orderWithProductWithValidationMessage($productData, $message, $productQty)
    {
        //Data
        $simple = $this->loadData($productData, null, array('general_name', 'general_sku'));

        $orderData = $this->loadData('order_newcustmoer_checkmoney_flatrate',
                array('filter_sku' => $simple['general_sku'], 'product_qty' => $productQty));
        $orderData = $this->arrayEmptyClear($orderData);
        $billingAddr = $orderData['billing_addr_data'];
        $shippingAddr = $orderData['shipping_addr_data'];
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->navigateToCreateOrderPage(null, $orderData['store_view']);
        $this->orderHelper()->addProductToOrder($orderData['products_to_add']['product_1']);
        $this->addParameter('sku', $simple['general_name']);
        $this->addParameter('qty', 10);
        $this->assertTrue($this->validationMessage($message), 'No Validation Message for Product');
        $this->orderHelper()->fillOrderAddress($billingAddr, $billingAddr['address_choice'], 'billing');
        $this->orderHelper()->fillOrderAddress($shippingAddr, $shippingAddr['address_choice'], 'shipping');
        $this->clickControl('link', 'get_shipping_methods_and_rates', FALSE);
        $this->pleaseWait();
        $this->orderHelper()->selectShippingMethod($orderData['shipping_data']);
        $this->orderHelper()->selectPaymentMethod($orderData['payment_data']);
        $this->orderHelper()->submitOreder();
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
        $this->orderInvoiceHelper()->createInvoiceAndVerifyProductQty();
        $this->orderShipmentHelper()->createShipmentAndVerifyProductQty();
        $this->orderCreditMemoHelper()->createCreditMemoAndVerifyProductQty('refund_offline');
    }

    public function dataProductWithValidationMessage()
    {
        return array(
            array('simple_low_qty', 'requested_quantity_not_available', 5),
            array('simple_out_of_stock', 'out_of_stock_product', 1),
            array('simple_min_allowed_qty', 'min_allowed_quantity_error', 5),
            array('simple_max_allowed_qty', 'max_allowed_quantity_error', 11),
            array('simple_with_increments', 'wrong_increments_qty', 5)
        );
    }

}
