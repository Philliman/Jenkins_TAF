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
 * Category deletion tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to Catalog -> Manage Categories</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_categories');
        $this->categoryHelper()->checkCategoriesPage();
    }

    /**
     * <p>Deleting Root Category</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category created</p>
     * <p>Steps:</p>
     * <p>Select Root Category</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Root category Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteRootCategory()
    {
        //Data
        $rootCategoryData = $this->loadData('root_category_required', null, 'name');
        //Steps
        $this->categoryHelper()->createRootCategory($rootCategoryData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        $this->assertTrue($this->successMessage('success_deleted_category'), $this->messages);
    }

    /**
     * <p>Deleting  Subcategory</p>
     * <p>Pre-Conditions:</p>
     * <p>Subcategory created</p>
     * <p>Steps:</p>
     * <p>Select created Subcategory</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Subcategory Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteSubCategory()
    {
        //Data
        $rootCat = 'Default Category';
        $subCategoryData = $this->loadData('sub_category_required', null, 'name');
        //Steps
        $this->categoryHelper()->createSubCategory($rootCat, $subCategoryData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->categoryHelper()->selectCategory($rootCat . '/' . $subCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        $this->assertTrue($this->successMessage('success_deleted_category'), $this->messages);
    }

    /**
     * <p>Deleting Root Category that assigned to store</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category created and assigned to store</p>
     * <p>Steps:</p>
     * <p>Select Root Category</p>
     * <p>Expected result</p>
     * <p>Verify that button "Delete" is absent on the page</p>
     *
     * @test
     */
    public function rootCategoryThatCannotBeDeleted()
    {
        //Data
        $rootCategoryData = $this->loadData('root_category_required', null, 'name');
        $storeData = $this->loadData('generic_store', array('root_category' => $rootCategoryData['name']));
        //Steps
        $this->categoryHelper()->createRootCategory($rootCategoryData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->checkCategoriesPage();
        //Steps
        $this->navigate('manage_stores');
        //Verifying
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        //Steps
        $this->storeHelper()->createStore($storeData, 'store');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_stores'), $this->messages);
        //Steps
        $this->navigate('manage_categories');
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        //Verifying
        $this->assertFalse($this->buttonIsPresent('delete_category'), 'There is "Delete Category" button on the page');
    }

    /**
     * <p>Deleting Root Category with Subcategory</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category Created</p>
     * <p>Subcategory created</p>
     * <p>Steps:</p>
     * <p>Select created Root Category</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Subcategory Deleted, Success message appears</p>
     *
     * @test
     */
    public function deleteRootCategoryWithSubcategories()
    {
        //Data
        $rootCategoryData = $this->loadData('root_category_required', null, 'name');
        $subCategoryData = $this->loadData('sub_category_required', null, 'name');
        //Steps
        $this->categoryHelper()->createRootCategory($rootCategoryData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        //Steps
        $this->categoryHelper()->createSubCategory($rootCategoryData['name'], $subCategoryData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        //Steps
        $this->pleaseWait();
        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
        //Verifying
        $this->assertTrue($this->successMessage('success_deleted_category'), $this->messages);
    }

    /**
     * <p>Deleting Root Category with Subcategory</p>
     * <p>Pre-Conditions:</p>
     * <p>Root Category Created</p>
     * <p>Subcategory created</p>
     * <p>Steps:</p>
     * <p>Select created Root Category</p>
     * <p>Click "Delete" button</p>
     * <p>Expected result</p>
     * <p>Subcategory Deleted, Success message appears</p>
     *
     * @test
     */
//    couldn't find the product throught the provided product data Phil 
//    public function deleteRootCategoryWithSubcategoriesHavingProducts()
//    {
//        //Data
//        $productData = $this->loadData('simple_product_required', null, array('general_name', 'general_sku'));
//        $rootCategoryData = $this->loadData('root_category_required', null, 'name');
//        $subCategoryData = $this->loadData('category_all',
//                array('category_products_search_sku' => $productData['general_sku']), 'name');
//        //Steps
//        $this->navigate('manage_products');
//        $this->assertTrue($this->checkCurrentPage('manage_products'), 'Wrong page is opened');
//        $this->addParameter('id', '0');
//        $this->productHelper()->createProduct($productData);
//        //Verifying
//        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
//        //Steps
//        $this->assertPreConditions();
//        $this->categoryHelper()->createRootCategory($rootCategoryData);
//        //Verifying
//        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
//        //Steps
//        $this->categoryHelper()->createSubCategory($rootCategoryData['name'], $subCategoryData);
//        //Verifying
//        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
//        //Steps
//        $this->pleaseWait();
//        $this->categoryHelper()->selectCategory($rootCategoryData['name']);
//        $this->categoryHelper()->deleteCategory('delete_category', 'confirm_delete');
//        //Verifying
//        $this->assertTrue($this->successMessage('success_deleted_category'), $this->messages);
//    }

}
