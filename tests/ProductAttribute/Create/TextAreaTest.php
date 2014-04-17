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
 * Create new product attribute. Type: Text Area
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttribute_Create_TextAreaTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to System -> Manage Attributes.</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_attributes');
        $this->assertTrue($this->checkCurrentPage('manage_attributes'), $this->messages);
        $this->addParameter('id', 0);
    }

    /**
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->buttonIsPresent('add_new_attribute'),
                'There is no "Add New Attribute" button on the page');
        $this->clickButton('add_new_attribute');
        $this->assertTrue($this->checkCurrentPage('new_product_attribute'), $this->messages);
        $this->assertTrue($this->buttonIsPresent('back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->buttonIsPresent('reset'), 'There is no "Reset" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_attribute'), 'There is no "Save" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_and_continue_edit'),
                'There is no "Save and Continue Edit" button on the page');
    }

    /**
     * <p>Create "Text Area" type Product Attribute (required fields only)</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Text Area" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill all required fields</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Text Area" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @depends navigation
     * @test
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $attrData = $this->loadData('product_attribute_textarea', null, array('attribute_code', 'admin_title'));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attributes'), $this->messages);

        return $attrData;
    }

    /**
     * <p>Checking of verification for duplicate of Product Attributes with similar code
     * Creation of new attribute with existing code.</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Text Area" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill 'Attribute Code' field by code used in test before.</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Text Area" type] shouldn't be created.</p>
     * <p>Error message: 'Attribute with the same code already exists' is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withAttributeCodeThatAlreadyExists(array $attrData)
    {
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->errorMessage('exists_attribute_code'), $this->messages);
    }

    /**
     * <p>Checking validation for required fields are EMPTY</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Text Area" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Skip filling of one field required and fill other required fields.</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Text Area" type] shouldn't be created.</p>
     * <p>Error JS message: 'This is a required field.' is displayed.</p>
     *
     * @dataProvider dataEmptyField
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withRequiredFieldsEmpty($emptyField)
    {
        //Data
        if ($emptyField == 'apply_to') {
            $attrData = $this->loadData('product_attribute_textarea', array($emptyField => 'Selected Product Types'),
                    'attribute_code');
        } else {
            $attrData = $this->loadData('product_attribute_textarea', array($emptyField => '%noValue%'),
                    'attribute_code');
        }
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        if ($emptyField != 'apply_to') {
            $fieldXpath = $this->_getControlXpath('field', $emptyField);
        } else {
            $fieldXpath = $this->_getControlXpath('multiselect', 'apply_product_types');
        }
        $this->addParameter('fieldXpath', $fieldXpath);
        $this->assertTrue($this->validationMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function dataEmptyField()
    {
        return array(
            array('attribute_code'),
            array('admin_title'),
            array('apply_to')
        );
    }

    /**
     * <p>Checking validation for valid data in the 'Attribute Code' field</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Text Area" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill 'Attribute Code' field by invalid data [Examples: '0xxx'/'_xxx'/'111']</p>
     * <p>4.Fill other required fields by regular data.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Text Area" type] shouldn't be created.</p>
     * <p>Error JS message: 'Please use only letters (a-z), numbers (0-9) or underscore(_) in
     * this field, first character should be a letter.' is displayed.</p>
     *
     * @dataProvider dataWrongCode
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withInvalidAttributeCode($wrongAttributeCode, $validationMessage)
    {
        //Data
        $attrData = $this->loadData('product_attribute_textarea', array('attribute_code' => $wrongAttributeCode));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->validationMessage($validationMessage), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function dataWrongCode()
    {
        return array(
            array('11code_wrong', 'invalid_attribute_code'),
            array('CODE_wrong', 'invalid_attribute_code'),
            array('wrong code', 'invalid_attribute_code'),
            array($this->generate('string', 11, ':punct:'), 'invalid_attribute_code'),
            array($this->generate('string', 33, ':lower:'), 'wrong_length_attribute_code')
        );
    }

    /**
     * <p>Checking of correct validate of submitting form by using special
     * characters for all fields exclude 'Attribute Code' field.</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Text Area" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill 'Attribute Code' field by regular data.</p>
     * <p>4.Fill other required fields by special characters.</p>
     * <p>5.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Text Area" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withSpecialCharactersInTitle()
    {
        //Data
        $attrData = $this->loadData('product_attribute_textarea',
                array('admin_title' => $this->generate('string', 32, ':punct:')), 'attribute_code');
        $attrData['admin_title'] = preg_replace('/<|>/', '', $attrData['admin_title']);
        $searchData = $this->loadData('attribute_search_data', array('attribute_code' => $attrData['attribute_code']));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attributes'), $this->messages);
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->productAttributeHelper()->verifyAttribute($attrData);
    }

    /**
     * <p>Checking of correct work of submitting form by using long values for fields filling</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Choose "Text Area" in 'Catalog Input Type for Store Owner' dropdown</p>
     * <p>3.Fill all required fields by long value alpha-numeric data.</p>
     * <p>4.Click on "Save Attribute" button</p>
     * <p>Expected result:</p>
     * <p>New attribute ["Text Area" type] successfully created.</p>
     * <p>Success message: 'The product attribute has been saved.' is displayed.</p>
     *
     * @depends withRequiredFieldsOnly
     * @test
     */
    public function withLongValues()
    {
        //Data
        $attrData = $this->loadData('product_attribute_textarea',
                array(
                    'attribute_code' => $this->generate('string', 30, ':lower:'),
                    'admin_title'    => $this->generate('string', 255, ':alnum:')
                )
        );
        $searchData = $this->loadData('attribute_search_data',
                array(
                    'attribute_code'  => $attrData['attribute_code'],
                    'attribute_lable' => $attrData['admin_title']
                )
        );
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attributes'), $this->messages);
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->productAttributeHelper()->verifyAttribute($attrData);
    }

}
