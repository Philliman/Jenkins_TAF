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
 * Test creation new store view
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Store_StoreView_CreateTest extends Mage_Selenium_TestCase
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
     * <p>1. Verify that 'Create Store View' button is present and click her.</p>
     * <p>2. Verify that the create store view page is opened.</p>
     * <p>3. Verify that 'Back' button is present.</p>
     * <p>4. Verify that 'Save Store View' button is present.</p>
     * <p>5. Verify that 'Reset' button is present.</p>
     *
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->controlIsPresent('button', 'create_store_view'),
                'There is no "Create Store View" button on the page');
        $this->clickButton('create_store_view');
        $this->assertTrue($this->checkCurrentPage('new_store_view'), $this->messages);
        $this->assertTrue($this->controlIsPresent('button', 'back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_store_view'),
                'There is no "Save" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'reset'), 'There is no "Reset" button on the page');
    }

    /**
     * <p>Create Store View. Fill in only required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @depends navigation
     * @test
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view');
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store_view'), $this->messages);

        return $storeViewData;
    }

    /**
     * <p>Create Store View.  Fill in field 'Code' by using code that already exist.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Code' field by using code that already exist.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withCodeThatAlreadyExists(array $storeViewData)
    {
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertTrue($this->errorMessage('store_view_code_exist'), $this->messages);
    }

    /**
     * <p>Create Store View. Fill in  required fields except one field.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields except one field.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @dataProvider dataEmptyField
     * @test
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view', array($emptyField => '%noValue%'));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $xpath = $this->_getControlXpath('field', $emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function dataEmptyField()
    {
        return array(
            array('store_view_name'),
            array('store_view_code'),
        );
    }

    /**
     * <p>Create Store View. Fill in only required fields. Use max long values for fields 'Name' and 'Code'</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in required fields by long value alpha-numeric data.</p>
     * <p>3. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created. Success Message is displayed.</p>
     * <p>Length of field "Name" is 255 characters. Length of field "Code" is 32 characters.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withLongValues()
    {
        //Data
        $longValues = array(
            'store_view_name' => $this->generate('string', 255, ':alnum:'),
            'store_view_code' => $this->generate('string', 32, ':lower:')
        );
        $storeViewData = $this->loadData('generic_store_view', $longValues);
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store_view'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
    }

    /**
     * <p>Create Store View. Fill in field 'Name' by using special characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Name' field by special characters.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSpecialCharactersInName()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view',
                array('store_view_name' => $this->generate('string', 32, ':punct:')));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store_view'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
    }

    /**
     * <p>Create Store View.  Fill in field 'Code' by using special characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Code' field by special characters.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSpecialCharactersInCode()
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view',
                array('store_view_code' => $this->generate('string', 32, ':punct:')));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertTrue($this->errorMessage('wrong_store_view_code'), $this->messages);
    }

    /**
     * <p>Create Store View.  Fill in field 'Code' by using wrong values.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Store View' button.</p>
     * <p>2. Fill in 'Code' field by wrong value.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Store View' button.</p>
     * <p>Expected result:</p>
     * <p>Store View is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @dataProvider dataInvalidCode
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withInvalidCode($invalidCode)
    {
        //Data
        $storeViewData = $this->loadData('generic_store_view', array('store_view_code' => $invalidCode));
        //Steps
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        //Verifying
        $this->assertTrue($this->errorMessage('wrong_store_view_code'), $this->messages);
    }

    public function dataInvalidCode()
    {
        return array(
            array('invalid code'),
            array('Invalid_code2'),
            array('2invalid_code2')
        );
    }

}
