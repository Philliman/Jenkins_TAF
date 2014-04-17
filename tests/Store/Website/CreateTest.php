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
 * Test creation new website
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Store_Website_CreateTest extends Mage_Selenium_TestCase
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
     * <p>1. Verify that 'Create Website' button is present and click her.</p>
     * <p>2. Verify that the create website page is opened.</p>
     * <p>3. Verify that 'Back' button is present.</p>
     * <p>4. Verify that 'Save Website' button is present.</p>
     * <p>5. Verify that 'Reset' button is present.</p>
     *
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->controlIsPresent('button', 'create_website'),
                'There is no "Create Website" button on the page');
        $this->clickButton('create_website');
        $this->assertTrue($this->checkCurrentPage('new_website'), $this->messages);
        $this->assertTrue($this->controlIsPresent('button', 'back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_website'), 'There is no "Save" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'reset'), 'There is no "Reset" button on the page');
    }

    /**
     * <p>Create Website. Fill in only required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Website' button.</p>
     * <p>2. Fill in required fields.</p>
     * <p>3. Click 'Save Website' button.</p>
     * <p>Expected result:</p>
     * <p>Website is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @depends navigation
     * @test
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $websiteData = $this->loadData('generic_website');
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_website'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);

        return $websiteData;
    }

    /**
     * <p>Create Website.  Fill in field 'Code' by using code that already exist.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Website' button.</p>
     * <p>2. Fill in 'Code' field by using code that already exist.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Website' button.</p>
     * <p>Expected result:</p>
     * <p>Website is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withCodeThatAlreadyExists(array $websiteData)
    {
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->errorMessage('website_code_exist'), $this->messages);
    }

    /**
     * <p>Create Website. Fill in all required fields except one field.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Website' button.</p>
     * <p>2. Fill in required fields except one field.</p>
     * <p>3. Click 'Save Website' button.</p>
     * <p>Expected result:</p>
     * <p>Website is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @dataProvider dataEmptyField
     * @test
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        $websiteData = $this->loadData('generic_website', array($emptyField => '%noValue%'));
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $xpath = $this->_getControlXpath('field', $emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function dataEmptyField()
    {
        return array(
            array('website_name'),
            array('website_code'),
        );
    }

    /**
     * <p>Create Website. Fill in only required fields. Use max long values for fields 'Name' and 'Code'</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Website' button.</p>
     * <p>2. Fill in required fields by long value alpha-numeric data.</p>
     * <p>3. Click 'Save Website' button.</p>
     * <p>Expected result:</p>
     * <p>Website is created. Success Message is displayed.</p>
     * <p>Length of field "Name" is 255 characters. Length of field "Code" is 32 characters.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withLongValues()
    {
        //Data
        $longValues = array(
            'website_name' => $this->generate('string', 255, ':alnum:'),
            'website_code' => $this->generate('string', 32, ':lower:')
        );
        $websiteData = $this->loadData('generic_website', $longValues);
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_website'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
    }

    /**
     * <p>Create Website. Fill in field 'Name' by using special characters.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Website' button.</p>
     * <p>2. Fill in 'Name' field by special characters.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Website' button.</p>
     * <p>Expected result:</p>
     * <p>Website is created.</p>
     * <p>Success Message is displayed</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSpecialCharacters_InName()
    {
        //Data
        $websiteData = $this->loadData('generic_website',
                array('website_name' => $this->generate('string', 32, ':punct:')));
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_website'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
    }

    /**
     * <p>Create Website.  Fill in field 'Code' by using wrong values.</p>
     * <p>Steps:</p>
     * <p>1. Click 'Create Website' button.</p>
     * <p>2. Fill in 'Code' field by wrong value.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save Website' button.</p>
     * <p>Expected result:</p>
     * <p>Website is not created.</p>
     * <p>Error Message is displayed.</p>
     *
     * @dataProvider dataInvalidCode
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withInvalidCode($invalidCode)
    {
        //Data
        $websiteData = $this->loadData('generic_website', array('website_code' => $invalidCode));
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->errorMessage('wrong_website_code'), $this->messages);
    }

    public function dataInvalidCode()
    {
        return array(
            array('invalid code'),
            array('Invalid_code2'),
            array('2invalid_code2'),
            array($this->generate('string', 32, ':punct:'))
        );
    }

    /**
     * <p>Create Website with several Stores assigned to one Root Category</p>
     * <p>Steps:</p>
     * <p>1. Create website</p>
     * <p>2. Create first store</p>
     * <p>3. Create second store</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSeveralStoresAssignedToOneRootCategory()
    {
        //1.1.Create website
        //Data
        $websiteData = $this->loadData('generic_website');
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_website'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        //1.2.Create two stores
        for ($i = 1; $i <= 2; $i++) {
            //Data
            $storeData = $this->loadData('generic_store', array('website' => $websiteData['website_name']));
            //Steps
            $this->storeHelper()->createStore($storeData, 'store');
            //Verifying
            $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
            $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        }
    }

    /**
     * <p>Create Website with several Store Views in one Store</p>
     * <p>Steps:</p>
     * <p>1. Create website</p>
     * <p>2. Create store</p>
     * <p>3. Create first store view</p>
     * <p>4. Create second store view</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSeveralStoresViewsInOneStore()
    {
        //1.1.Create website
        //Data
        $websiteData = $this->loadData('generic_website');
        //Steps
        $this->storeHelper()->createStore($websiteData, 'website');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_website'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        //1.2.Create store
        //Data
        $storeData = $this->loadData('generic_store', array('website' => $websiteData['website_name']));
        //Steps
        $this->storeHelper()->createStore($storeData, 'store');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        //1.3.Create two store view
        for ($i = 1; $i <= 2; $i++) {
            //Data
            $storeViewData = $this->loadData('generic_store_view', array('store_name' => $storeData['store_name']));
            //Steps
            $this->storeHelper()->createStore($storeViewData, 'store_view');
            //Verifying
            $this->assertTrue($this->successMessage('success_saved_store_view'), $this->messages);
            $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        }
    }

}
