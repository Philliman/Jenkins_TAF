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
 * @TODO
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category_MoveTestCase extends Mage_Selenium_TestCase  // name changed from Category_MoveTest to Category_MoveTestCase as this will prevent this being trigered to test. 
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
     * <p>Move Root Category to Root category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 2 root categories;</p>
     * <p>3.Move first root category to second one;</p>
     * <p>Expected result:</p>
     * <p>Category is moved successfully</p>
     *
     * @test
     */
    public function rootCategoryToRoot()
    {
        $categoryDataFrom = $this->loadData('root_category_required', null, 'name');
        $categoryDataTo = $this->loadData('root_category_required', null, 'name');
        $this->categoryHelper()->createRootCategory($categoryDataFrom);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->createRootCategory($categoryDataTo);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->moveCategory($categoryDataFrom['name'], $categoryDataTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory($categoryDataTo['name'] . '/' . $categoryDataFrom['name']);
    }

    /**
     * <p>Move Root with Sub Category to Root category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 2 root categories and 1 sub category;</p>
     * <p>3.Move first root category with sub to second root;</p>
     * <p>Expected result:</p>
     * <p>Category is moved successfully</p>
     *
     * @test
     */
    public function rootWithSubToRoot()
    {
        $categoryDataFrom = $this->loadData('root_category_required', null, 'name');
        $categoryDataSub = $this->loadData('sub_category_required', null, 'name');
        $categoryDataTo = $this->loadData('root_category_required', null, 'name');
        $this->categoryHelper()->createRootCategory($categoryDataFrom);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->createSubCategory($categoryDataFrom['name'], $categoryDataSub);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->createRootCategory($categoryDataTo);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->moveCategory($categoryDataFrom['name'], $categoryDataTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory($categoryDataTo['name'] .
                '/' . $categoryDataFrom['name'] . '/' . $categoryDataSub['name']);
    }

    /**
     * <p>Move Sub Category to Sub category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 2 root categories and 2 sub category;</p>
     * <p>3.Move first sub category to second sub;</p>
     * <p>Expected result:</p>
     * <p>Category is moved successfully</p>
     *
     * @test
     */
    public function subToSubNestedCategory()
    {
        $categoryDataFrom = $this->loadData('root_category_required', null, 'name');
        $categoryDataSubFrom = $this->loadData('sub_category_required', null, 'name');
        $categoryDataTo = $this->loadData('root_category_required', null, 'name');
        $categoryDataSubTo = $this->loadData('sub_category_required', null, 'name');
        $this->categoryHelper()->createRootCategory($categoryDataFrom);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->createSubCategory($categoryDataFrom['name'], $categoryDataSubFrom);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->createRootCategory($categoryDataTo);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->createSubCategory($categoryDataTo['name'], $categoryDataSubTo);
        $this->categoryHelper()->moveCategory($categoryDataSubFrom['name'], $categoryDataSubTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory($categoryDataTo['name'] .
                '/' . $categoryDataSubTo['name'] . '/' . $categoryDataSubFrom['name']);
    }

    /**
     * <p>Move Root Category assigned to store to Root category.</p>
     * <p>Steps:</p>
     * <p>1.Go to Manage Categories;</p>
     * <p>2.Create 1 root category;</p>
     * <p>3.Create website with store;</p>
     * <p>4.Assign created root category to store</p>
     * <p>4.Create another root categry</p>
     * <p>3.Move first root category assigned to store to second root;</p>
     * <p>Expected result:</p>
     * <p>Category is not moved</p>
     *
     * @test
     */
    public function rootCategoryAssignedToWebsite()
    {
        //Data
        $categoryDataFrom = $this->loadData('root_category_required', null, 'name');
        $websiteData = $this->loadData('generic_website');
        $storeData = $this->loadData('generic_store',
                array('website' => $websiteData['website_name'], 'root_category' => $categoryDataFrom['name']));
        $categoryDataTo = $this->loadData('root_category_required', null, 'name');
        //Create category to assign to store
        $this->navigate('manage_categories');
        $this->categoryHelper()->createRootCategory($categoryDataFrom);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        //Create Website and Store. Assign root category to store
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($websiteData, 'website');
        $this->assertTrue($this->successMessage('success_saved_website'), $this->messages);
        $this->storeHelper()->createStore($storeData, 'store');
        $this->assertTrue($this->successMessage('success_saved_store'), $this->messages);
        //Try to move assigned to store root category
        $this->navigate('manage_categories');
        $this->categoryHelper()->createRootCategory($categoryDataTo);
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);
        $this->categoryHelper()->moveCategory($categoryDataFrom['name'], $categoryDataTo['name']);
        //Verification
        $this->categoryHelper()->selectCategory($categoryDataFrom['name']);
    }
}
