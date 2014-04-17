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
 * Duplicate product tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product_DuplicateTest extends Mage_Selenium_TestCase
{

    /**
     * <p>Login to backend</p>
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
        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        $this->addParameter('id', '0');
    }

    /**
     * Test Realizing precondition for creating configurable product.
     *
     * @test
     */
    public function createConfigurableAttribute()
    {
        //Data
        $attrData = $this->loadData('product_attribute_dropdown_with_options', null,
                array('admin_title', 'attribute_code'));
        $associatedAttributes = $this->loadData('associated_attributes',
                array('General' => $attrData['attribute_code']));
        //Steps
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        //Steps
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        //Verifying
        $this->assertTrue($this->successMessage('success_attribute_set_saved'), $this->messages);

        return $attrData;
    }

    /**
     * Test Realizing precondition for duplicating products.
     *
     * @test
     * @depends createConfigurableAttribute
     */
    public function createProducts($attrData)
    {
        //Data
        $simple = $this->loadData('simple_product_for_order', null, array('general_name', 'general_sku'));
        $simple['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_1']['admin_option_name'];
        $virtual = $this->loadData('virtual_product_for_order', null, array('general_name', 'general_sku'));
        $virtual['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_2']['admin_option_name'];
        $downloadable = $this->loadData('downloadable_product_for_order',
                array('downloadable_links_purchased_separately' => 'No'), array('general_name', 'general_sku'));
        $downloadable['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_3']['admin_option_name'];

        $productData = array('simple' => $simple, 'downloadable' => $downloadable, 'virtual' => $virtual);
        //Steps
        foreach ($productData as $key => $value) {
            $this->productHelper()->createProduct($value, $key);
            //Verifying
            $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
            $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        }

        return $productData;
    }

    /**
     * <p>Creating duplicated simple product</p>
     * <p>Steps:</p>
     * <p>1. Open created product;</p>
     * <p>2. Click "Duplicate" button;</p>
     * <p>3. Verify that all fields has the same data except SKU and Status(fields empty)</p>
     * <p>Expected result:</p>
     * <p>Product is duplicated, confirmation message appears;</p>
     *
     * @depends createConfigurableAttribute
     * @depends createProducts
     * @test
     */
    public function duplicateSimple($attrData, $productData)
    {
        //Data
        $simple = $this->loadData('duplicate_simple',
                array(
                    'related_search_sku'           => $productData['simple']['general_sku'],
                    'related_product_position'     => 10,
                    'up_sells_search_sku'          => $productData['virtual']['general_sku'],
                    'up_sells_product_position'    => 20,
                    'cross_sells_search_sku'       => $productData['downloadable']['general_sku'],
                    'cross_sells_product_position' => 30
                ), array('general_name', 'general_sku'));
        $simple['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_1']['admin_option_name'];
        $productSearch = $this->loadData('product_search', array('product_sku' => $simple['general_sku']));
        //Steps
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertTrue($this->successMessage('success_duplicated_product'), $this->messages);
        $this->productHelper()->verifyProductInfo($simple, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating duplicated virtual product</p>
     * <p>Steps:</p>
     * <p>1. Open created product;</p>
     * <p>2. Click "Duplicate" button;</p>
     * <p>3. Verify that all fields has the same data except SKU and Status(fields empty)</p>
     * <p>Expected result:</p>
     * <p>Product is duplicated, confirmation message appears;</p>
     *
     * @depends createConfigurableAttribute
     * @depends createProducts
     * @test
     */
    public function duplicateVirtual($attrData, $productData)
    {
        //Data
        $virtual = $this->loadData('duplicate_virtual',
                array(
                    'related_search_sku'           => $productData['simple']['general_sku'],
                    'related_product_position'     => 10,
                    'up_sells_search_sku'          => $productData['virtual']['general_sku'],
                    'up_sells_product_position'    => 20,
                    'cross_sells_search_sku'       => $productData['downloadable']['general_sku'],
                    'cross_sells_product_position' => 30
                ), array('general_name', 'general_sku'));
        $virtual['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_2']['admin_option_name'];
        $productSearch = $this->loadData('product_search', array('product_sku' => $virtual['general_sku']));
        //Steps
        $this->productHelper()->createProduct($virtual, 'virtual');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertTrue($this->successMessage('success_duplicated_product'), $this->messages);
        $this->productHelper()->verifyProductInfo($virtual, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating duplicated downloadable product</p>
     * <p>Steps:</p>
     * <p>1. Open created product;</p>
     * <p>2. Click "Duplicate" button;</p>
     * <p>3. Verify that all fields has the same data except SKU and Status(fields empty)</p>
     * <p>Expected result:</p>
     * <p>Product is duplicated, confirmation message appears;</p>
     *
     * @depends createConfigurableAttribute
     * @depends createProducts
     * @test
     */
    public function duplicateDownloadable($attrData, $productData)
    {
        //Data
        $downloadable = $this->loadData('duplicate_downloadable',
                array(
                    'related_search_sku'           => $productData['simple']['general_sku'],
                    'related_product_position'     => 10,
                    'up_sells_search_sku'          => $productData['virtual']['general_sku'],
                    'up_sells_product_position'    => 20,
                    'cross_sells_search_sku'       => $productData['downloadable']['general_sku'],
                    'cross_sells_product_position' => 30
                ), array('general_name', 'general_sku'));
        $downloadable['general_user_attr']['dropdown'][$attrData['attribute_code']] =
                $attrData['option_3']['admin_option_name'];
        $productSearch = $this->loadData('product_search', array('product_sku' => $downloadable['general_sku']));
        //Steps
        $this->productHelper()->createProduct($downloadable, 'downloadable');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertTrue($this->successMessage('success_duplicated_product'), $this->messages);
        $this->productHelper()->verifyProductInfo($downloadable, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating grouped product with assosiated products</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>6. Open created product;</p>
     * <p>7. Click "Duplicate" button;</p>
     * <p>8. Verify required fields has the same data except SKU (field empty)</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @depends createProducts
     *
     * @test
     */
    public function duplicateGrouped($productData)
    {
        //Data
        $grouped = $this->loadData('duplicate_grouped',
                array(
                    'related_search_sku'           => $productData['simple']['general_sku'],
                    'related_product_position'     => 10,
                    'up_sells_search_sku'          => $productData['virtual']['general_sku'],
                    'up_sells_product_position'    => 20,
                    'cross_sells_search_sku'       => $productData['downloadable']['general_sku'],
                    'cross_sells_product_position' => 30
                ), array('general_name', 'general_sku'));

        $grouped['associated_grouped_data']['associated_grouped_1']['associated_search_sku'] =
                $productData['simple']['general_sku'];
        $grouped['associated_grouped_data']['associated_grouped_2']['associated_search_sku'] =
                $productData['virtual']['general_sku'];
        $grouped['associated_grouped_data']['associated_grouped_3']['associated_search_sku'] =
                $productData['downloadable']['general_sku'];
        $productSearch = $this->loadData('product_search', array('product_sku' => $grouped['general_sku']));
        //Steps
        $this->productHelper()->createProduct($grouped, 'grouped');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertTrue($this->successMessage('success_duplicated_product'), $this->messages);
        $this->productHelper()->verifyProductInfo($grouped, array('general_sku', 'general_status'));
    }

    /**
     * <p>Creating duplicated Bundle Product</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>6. Open created product;</p>
     * <p>7. Click "Duplicate" button;</p>
     * <p>8. Verify required fields has the same data except SKU (field empty)</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @depends createProducts
     * @dataProvider dataBundle
     * @test
     */
    public function duplicateBundle($data, $productData)
    {
        //Data
        $bundle = $this->loadData($data,
                array(
                    'related_search_sku'           => $productData['simple']['general_sku'],
                    'related_product_position'     => 10,
                    'up_sells_search_sku'          => $productData['virtual']['general_sku'],
                    'up_sells_product_position'    => 20,
                    'cross_sells_search_sku'       => $productData['downloadable']['general_sku'],
                    'cross_sells_product_position' => 30
                ), array('general_name', 'general_sku'));

        $bundle['bundle_items_data']['item_1']['add_product_1']['bundle_items_search_sku'] =
                $productData['simple']['general_sku'];
        $bundle['bundle_items_data']['item_2']['add_product_1']['bundle_items_search_sku'] =
                $productData['virtual']['general_sku'];
        $productSearch = $this->loadData('product_search', array('product_sku' => $bundle['general_sku']));
        //Steps
        $this->productHelper()->createProduct($bundle, 'bundle');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertTrue($this->successMessage('success_duplicated_product'), $this->messages);
        $this->productHelper()->verifyProductInfo($bundle, array('general_sku', 'general_status'));
    }

    public function dataBundle()
    {
        return array(
            array('duplicate_fixed_bundle'),
            array('duplicate_dynamic_bundle'),
        );
    }

    /**
     * <p>Duplicate Configurable product with assosiated products</p>
     * <p>Preconditions</p>
     * <p>Attribute Set created</p>
     * <p>Virtual product created</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add product' button;</p>
     * <p>2. Fill in 'Attribute Set' and 'Product Type' fields;</p>
     * <p>3. Click 'Continue' button;</p>
     * <p>4. Fill in all required fields;</p>
     * <p>5. Goto "Associated products" tab;</p>
     * <p>6. Select created Virtual product;</p>
     * <p>5. Click 'Save' button;</p>
     * <p>6. Open created product;</p>
     * <p>7. Click "Duplicate" button;</p>
     * <p>8. Verify required fields has the same data except SKU (field empty)</p>
     *
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @test
     * @depends createConfigurableAttribute
     * @depends createProducts
     */
    public function duplicateConfigurable($attrData, $productData)
    {
        //Data
        $configur = $this->loadData('duplicate_configurable',
                array(
                    'configurable_attribute_title' => $attrData['admin_title'],
                    'related_search_sku'           => $productData['simple']['general_sku'],
                    'related_product_position'     => 10,
                    'up_sells_search_sku'          => $productData['virtual']['general_sku'],
                    'up_sells_product_position'    => 20,
                    'cross_sells_search_sku'       => $productData['downloadable']['general_sku'],
                    'cross_sells_product_position' => 30
                ), array('general_name', 'general_sku'));
        $productSearch = $this->loadData('product_search', array('product_sku' => $configur['general_sku']));
        //Steps
        $this->productHelper()->createProduct($configur, 'configurable');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        //Steps
        $this->productHelper()->openProduct($productSearch);
        $this->clickButton('duplicate');
        //Verifying
        $this->assertTrue($this->successMessage('success_duplicated_product'), $this->messages);
        //Steps
        $this->productHelper()->fillConfigurableSettings($configur);
        //Verifying
        $this->productHelper()->verifyProductInfo($configur,
                array('general_sku', 'general_status', 'configurable_attribute_title'));
    }

}
