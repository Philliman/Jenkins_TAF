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
 * Creating order for new customer
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Order_Create_NewCustomerTest extends Mage_Selenium_TestCase
{

    /**
     * <p>Preconditions:</p>
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
     * Create Simple Product for tests
     *
     * @test
     */
    public function createSimpleProduct()
    {
        //Data
        $simpleSku = $this->loadData('simple_product_for_order', NULL, array('general_name', 'general_sku'));
        //Steps
        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        $this->productHelper()->createProduct($simpleSku);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);

        return $simpleSku['general_sku'];
    }

    /**
     * <p>Create customer via 'Create order' without saving address</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address(not check 'save in address book');</p>
     * <p>8.Choose in shipping address the same as billing
     *      (not check 'save in address book');</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer successfully created without address.
     *    Order is created for the new customer</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function newCustomerWithoutAddress($simpleSku)
    {
        //Data
        $orderData = $this->loadData('order_physical',
                array('filter_sku' => $simpleSku, 'customer_email' => $this->generate('email', 32, 'valid')));
        $param = $orderData['billing_addr_data']['billing_first_name'] . ' '
                . $orderData['billing_addr_data']['billing_last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $searchCustomer = $this->loadData('search_customer',
                array('email' => $orderData['account_data']['customer_email']));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($searchCustomer);
        $this->openTab('addresses');
        $xpath = $this->_getControlXpath('fieldset', 'list_customer_addresses') . '//li';
        $addressCount = $this->getXpathCount($xpath);
        $this->assertEquals(0, $addressCount, 'Customer should not have address, but have ' . $addressCount);
    }

    /**
     * <p>Create customer via 'Create order' with saving address</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address(check 'save in address book');</p>
     * <p>8.Choose in shipping address the same as billing(check 'save in address book');</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is created with addresses. Order is created for the new customer;</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function newCustomerWithAddress($simpleSku)
    {
        //Data
        $orderData = $this->loadData('order_physical',
                array('filter_sku' => $simpleSku, 'customer_email' => $this->generate('email', 32, 'valid')));
        $orderData['billing_addr_data'] = $this->loadData('billing_address_all');
        $orderData['shipping_addr_data'] = $this->loadData('shipping_address_all');
        $param = $orderData['billing_addr_data']['billing_first_name'] . ' '
                . $orderData['billing_addr_data']['billing_last_name'];
        $this->addParameter('customer_first_last_name', $param);
        $searchCustomer = $this->loadData('search_customer',
                array('email' => $orderData['account_data']['customer_email']));
        $addressVerify[] = $this->loadData('billing');
        $addressVerify[] = $this->loadData('shipping');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer($searchCustomer);
        $this->openTab('addresses');
        foreach ($addressVerify as $value) {
            $addressNumber = $this->customerHelper()->isAddressPresent($value);
            $this->assertNotEquals(0, $addressNumber, 'The specified address is not present.');
        }
    }

    /**
     * <p>Create customer via 'Create order' form (use exist email).</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function newCustomerWithExistEmail($simpleSku)
    {
        //Data
        $userData = $this->loadData('generic_customer_account', NULL, 'email');
        $orderData = $this->loadData('order_newcustmoer_checkmoney_flatrate',
                array('filter_sku' => $simpleSku, 'customer_email' => $userData['email']));
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_customer'), $this->messages);
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->errorMessage('customer_email_already_exists'), $this->messages);
    }

    /**
     * <p>Create customer via 'Create order' form (use long email).</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function newCustomerWithLongEmail($simpleSku)
    {
        //Data
        $email = $this->generate('string', 129, ':alnum:') . '@example.com';
        $orderData = $this->loadData('order_newcustmoer_checkmoney_flatrate',
                array('filter_sku' => $simpleSku, 'customer_email' => $email));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->errorMessage('email_exceeds_allowed_length'), $this->messages);
    }

    /**
     * <p>Create customer via 'Create order' form (not correct email).</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function newCustomerWithNotCorrectEmail($simpleSku)
    {
        //Data
        $email = $this->generate('string', 23, ':alnum:') . '@' . $this->generate('string', 65, ':alnum:') . '.org';
        $orderData = $this->loadData('order_newcustmoer_checkmoney_flatrate',
                array('filter_sku' => $simpleSku, 'customer_email' => $email));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->errorMessage('email_is_not_valid_hostname'), $this->messages);
        $this->assertTrue($this->errorMessage('not_valid_hostname'), $this->messages);
        $this->assertTrue($this->errorMessage('hostname_not_valid'), $this->messages);
    }

    /**
     * <p>Create customer via 'Create order' form</p>
     * <p>Create order.</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View;</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address;</p>
     * <p>8.Choose in shipping address the same as billing;</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function newCustomerWithNotValidEmail($simpleSku)
    {
        //Data
        $orderData = $this->loadData('order_newcustmoer_checkmoney_flatrate',
                array('filter_sku' => $simpleSku, 'customer_email' => $this->generate('email', 23, 'invalid')));
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->validationMessage('not_valid_email'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    /**
     * <p>Create customer via 'Create order' form.</p>
     * <p>Create order(all fields are filled).</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales->Orders;</p>
     * <p>2.Press "Create New Order" button;</p>
     * <p>3.Press "Create New Customer" button;</p>
     * <p>4.Choose Store View</p>
     * <p>5.Press 'Add Products' button;</p>
     * <p>6.Add product;</p>
     * <p>7.Fill in billing address(use long values);</p>
     * <p>8.Fill in shipping address(use long values);</p>
     * <p>9.Check shipping method;</p>
     * <p>10.Check payment method 'Check / Money order';</p>
     * <p>11. Submit order;</p>
     * <p>Expected result:</p>
     * <p>New customer is not created. Order is not created for the new customer;</p>
     *
     * @depends createSimpleProduct
     * @test
     */
    public function orderCompleteReqFields($simpleSku)
    {
        //Data
        $orderData = $this->loadData('order_newcustmoer_checkmoney_flatrate',
                array('filter_sku' => $simpleSku, 'customer_email' => $this->generate('email', 22, 'valid')));
        $orderData['billing_addr_data'] = $this->orderHelper()->customerAddressGenerator(':alnum:', 'billing', 255);
        $orderData['shipping_addr_data'] = $this->orderHelper()->customerAddressGenerator(':alnum:', 'shipping', 255);
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData);
        //Verifying
        $this->assertTrue($this->successMessage('success_created_order'), $this->messages);
    }

}
