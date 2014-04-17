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
 * Test creation new Store.
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Store_Store_CreateTest extends Mage_Selenium_TestCase
{

    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Stores</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_stores');
    }

    /**
     * <p>Test navigation.</p>
     * <p>Steps:</p>
     * <p>1. Verify that 'Create Store' button is present and click her.</p>
     * <p>2. Verify that the create store page is opened.</p>
     * <p>3. Verify that 'Back' button is present.</p>
     * <p>4. Verify that 'Save Store' button is present.</p>
     * <p>5. Verify that 'Reset' button is present.</p>
     *
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->controlIsPresent('button', 'create_store'),
                'There is no "Create Store" button on the page');
        $this->clickButton('create_store');
        $this->assertTrue($this->controlIsPresent('button', 'back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_store'), 'There is no "Save" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'reset'), 'There is no "Reset" button on the page');
    }

    /**
     * <p>Create Store. Fill in only required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store' button.</p>
     * <p>2. Fill in required fields.</p>
     * <p>3. Click 'Save Store' button.</p>
     * <p>Expected result:</p>
     * <p>Store is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @test
     */
    public function withRequiredFieldsOnly()
    {
        //Steps
        $this->storeHelper()->createStore('generic_store', 'store');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
    }

    /**
     * <p>Create Store. Fill in required fields except one field.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store' button.</p>
     * <p>2. Fill in fields except one required field.</p>
     * <p>3. Click 'Save Store' button.</p>
     * <p>Expected result:</p>
     * <p>Store is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @dataProvider dataEmptyField
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withRequiredFieldsEmpty($emptyField, $fieldType)
    {
        //Data
        $storeData = $this->loadData('generic_store', array($emptyField => '%noValue%'));
        //Steps
        $this->storeHelper()->createStore($storeData, 'store');
        //Verifying
        $xpath = $this->_getControlXpath($fieldType, $emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    /**
     * <p>Data for withRequiredFieldsEmpty</p>
     * @return array
     */
    public function dataEmptyField()
    {
        return array(
            array('store_name', 'field'),
            array('root_category', 'dropdown'),
        );
    }

    /**
     * <p>Create Store. Fill in only required fields. Use max long values for field 'Name'</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store' button.</p>
     * <p>2. Fill in required fields by long value alpha-numeric data.</p>
     * <p>3. Click 'Save Store' button.</p>
     * <p>Expected result:<p>
     * <p>Store is created. Success Message is displayed.</p>
     * <p>Length of field "Name" is 255 characters.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withLongValues()
    {
        //Data
        $storeData = $this->loadData('generic_store',
                array('store_name' => $this->generate('string', 255, ':alnum:')));
        //Steps
        $this->storeHelper()->createStore($storeData, 'store');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
    }

    /**
     * <p>Create Store. Fill in field 'Name' by using special characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store' button.</p>
     * <p>2. Fill in 'Name' field by special characters.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store' button.</p>
     * <p>Expected result:</p>
     * <p>Store is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSpecialCharactersInName()
    {
        //Data
        $storeData = $this->loadData('generic_store',
                array('store_name' => $this->generate('string', 32, ':punct:')));
        //Steps
        $this->storeHelper()->createStore($storeData, 'store');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
    }

}
