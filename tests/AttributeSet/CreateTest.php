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
 * Attribute Set creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSet_CreateTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to Catalog -> Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_attribute_sets');
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);
        $this->addParameter('id', '0');
    }

    /**
     * <p>TL-MAGE-74:Attribute Set creation - based on Default</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in fields</p>
     * <p>3. Click button "Save Attribute Set"</p>
     * <p>Expected result</p>
     * <p>Received the message on successful completion of the attribute set creation</p>
     *
     * @test
     */
    public function basedOnDefault()
    {
        //Data
        $setData = $this->loadData('attribute_set', null, 'set_name');
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);
        return $setData['set_name'];
    }

    /**
     * <p>TL-MAGE-76:Attribute Set creation - existing name</p>
     * <p>Preconditions:</p>
     * <p>Attribute set created based on default</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in fields - type existing Attribute Set name in "Name" field</p>
     * <p>3. Click button "Save Attribute Set"</p>
     * <p>Expected result</p>
     * <p>Received error message "Attribute set with the "attrSet_name" name already exists."</p>
     *
     * @depends basedOnDefault
     * @test
     */
    public function withNameThatAlreadyExists($attributeSetName)
    {
        //Data
        $setData = $this->loadData('attribute_set', array('set_name' => $attributeSetName));
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->addParameter('attributeSetName', $setData['set_name']);
        $this->assertTrue($this->errorMessage('error_attribute_set_exist'), $this->messages);
    }

    /**
     * <p>TL-MAGE-75:Attribute Set creation - empty name</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Click button "Save Attribute Set"</p>
     * <p>Expected result</p>
     * <p>Received error message "This is a required field."</p>
     *
     * @depends basedOnDefault
     * @test
     */
    public function withEmptyName()
    {
        //Data
        $setData = $this->loadData('attribute_set', array('set_name' => ''));
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->addFieldIdToMessage('field', 'set_name');
        $this->assertTrue($this->validationMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    /**
     * <p>Creating Attribute Set with long values in required fields</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in "Name" field by long values;</p>
     * <p>3. Click button "Save Attribute Set"</p>
     * <p>Expected result:</p>
     * <p>Received the message on successful completion of the attribute set creation</p>
     *
     * @depends basedOnDefault
     * @test
     */
    public function withLongValues()
    {
        //Data
        $setData = $this->loadData('attribute_set', array('set_name' => $this->generate('string', 255, ':alnum:')));
        $attributeSetSearch['set_name'] = $setData['set_name'];
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);
        //Steps
        $this->attributeSetHelper()->openAttributeSet($attributeSetSearch);
        $this->assertTrue($this->verifyForm($attributeSetSearch), $this->messages);
    }

    /**
     * <p>Creating Attribute Set using special characters for set name</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in "Name" field using special characters;</p>
     * <p>3. Click button "Save Attribute Set"</p>
     * <p>Expected result:</p>
     * <p>Received the message on successful completion of the attribute set creation</p>
     *
     * @depends basedOnDefault
     * @test
     */
    public function withSpecialCharacters()
    {
        //Data
        $setData = $this->loadData('attribute_set', array('set_name' => $this->generate('string', 32, ':punct:')));
        $setData['set_name'] = preg_replace('/<|>/', '', $setData['set_name']);
        $attributeSetSearch['set_name'] = $setData['set_name'];
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);
        //Steps
        $this->attributeSetHelper()->openAttributeSet($attributeSetSearch);
        $this->assertTrue($this->verifyForm($attributeSetSearch), $this->messages);
    }

    /**
     * <p>TL-MAGE-77:Add user product attributes</p>
     * <p>Preconditions</p>
     * <p>Product Attribute created</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in "Name" field</p>
     * <p>3. Click button "Add New" in Groups</p>
     * <p>4. Fill in "Name" field</p>
     * <p>5. Assign user product  Attributes* to "User Attributes' group</p>
     * <p>6. Click button "Save Attribute Set"</p>
     * <p>Expected result:</p>
     * <p>Received the message on successful completion of the attribute set creation</p>
     *
     * @depends basedOnDefault
     * @test
     */
    public function addUserProductAttributesToNewGroup()
    {
        //Data
        $groupName = $this->generate('string', 5, ':lower:') . '_test_group';
        $attrData = $this->loadData('product_attributes', null, array('attribute_code', 'admin_title'));
        $setData = $this->loadData('attribute_set', null, 'set_name');
        $attrCodes = array();
        foreach ($attrData as $key => $value) {
            if (is_array($value) && array_key_exists('attribute_code', $value)) {
                $attrCodes[] = $value['attribute_code'];
            }
        }
        $setData['associated_attributes'][$groupName] = $attrCodes;
        //Steps
        $this->navigate('manage_attributes');
        $this->assertTrue($this->checkCurrentPage('manage_attributes'), $this->messages);
        foreach ($attrData as $value) {
            $this->productAttributeHelper()->createAttribute($value);
            //Verifying
            $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
            $this->assertTrue($this->checkCurrentPage('manage_attributes'), $this->messages);
        }
        //Steps
        $this->assertPreConditions();
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);

        return $setData;
    }

    /**
     * <p>Attribute Set creation - based on Custom</p>
     * <p>Preconditions:</p>
     * <p>Attribute set created based on default</p>
     * <p>Steps</p>
     * <p>1. Click button "Add New Set"</p>
     * <p>2. Fill in fields - choose existing Attribute Set in "Based On" field</p>
     * <p>3. Click button "Save Attribute Set"</p>
     * <p>Expected result</p>
     * <p>Received the message on successful completion of the attribute set creation</p>
     *
     * @depends addUserProductAttributesToNewGroup
     * @test
     */
    public function basedOnCustom($setData)
    {
        //Data
        $setDataCustom = $this->loadData('attribute_set', array('based_on' => $setData['set_name']), 'set_name');
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setDataCustom);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);
    }

}
