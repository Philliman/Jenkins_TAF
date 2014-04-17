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
 * Attribute Set deletion Tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSet_DeleteTest extends Mage_Selenium_TestCase
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
     * @test
     */
    public function withoutProducts()
    {
        //Data
        $setData = $this->loadData('attribute_set', null, 'set_name');
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attribute_sets'), $this->messages);
        //Steps
        $this->attributeSetHelper()->openAttributeSet($setData['set_name']);
        $this->clickButtonAndConfirm('delete_attribute_set', 'confirmation_for_delete');
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_deleted'), $this->messages);
    }

    /**
     * @test
     */
    public function withProducts()
    {
        //Data
        $setData = $this->loadData('attribute_set', null, 'set_name');
        $productData = $this->loadData('simple_product_required',
                array('product_attribute_set' => $setData['set_name']), array('general_name', 'general_sku'));
        $searchProduct = $this->loadData('product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->attributeSetHelper()->createAttributeSet($setData);
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        //Steps
        $this->assertPreConditions();
        $this->attributeSetHelper()->openAttributeSet($setData['set_name']);
        $this->clickButtonAndConfirm('delete_attribute_set', 'confirmation_for_delete');
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_deleted'), $this->messages);
        $this->navigate('manage_products');
        $xpath = $this->search($searchProduct);
        $this->assertEquals(null, $xpath, 'Product is not deleted');
    }

    /**
     * @test
     */
    public function deleteDefaultSet()
    {
        $this->attributeSetHelper()->openAttributeSet('Default');
        $this->assertFalse($this->buttonIsPresent('delete_attribute_set'), 'There is "Delete" button on the page');
    }

}
