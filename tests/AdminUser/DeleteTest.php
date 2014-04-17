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
 * Deleting Admin User
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AdminUser_DeleteTest extends Mage_Selenium_TestCase
{

    /**
     * Log in to Backend.
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Permissions -> Users.</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_admin_users');
        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);
        $this->addParameter('id', '0');
    }

    /**
     * <p>Create Admin User (all required fields are filled).</p>
     * <p>Steps:</p>
     * <p>1.Press "Add New User" button.</p>
     * <p>2.Fill all required fields.</p>
     * <p>3.Press "Save User" button.</p>
     * <p>4.Search the created user.</p>
     * <p>5.Press "Delete User" button.</p>
     * <p>Expected result:</p>
     * <p>User successfully deleted.</p>
     * <p>Message "The user has been deleted." is displayed.</p>
     *
     * @test
     */
    public function DeleteAdminUserDeletable()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', null, array('email', 'user_name'));
        $searchData = $this->loadData('search_admin_user',
                array('email' => $userData['email'], 'user_name' => $userData['user_name']));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);
        //Searching
        $this->addParameter('user_first_last_name',
                $searchData['first_name'] . ' ' . $searchData['last_name']);
        $this->assertTrue($this->searchAndOpen($searchData), 'Admin User is not found');
        //Steps
        $this->clickButtonAndConfirm('delete_user', 'confirmation_for_delete');
        //Verifying
        $this->assertTrue($this->successMessage('success_deleted_user'), $this->messages);
    }

    /**
     * <p>Delete logged in as Admin User</p>
     *
     * @test
     */
    public function DeleteAdminUserCurrent()
    {
        //Data
        $searchData = $this->loadData('search_admin_user');
        $searchDataCurrentUser = array();
        //Steps
        $this->navigate('my_account');
        $this->assertTrue($this->checkCurrentPage('my_account'), $this->messages);
        foreach ($searchData as $key => $value) {
            if ($value != '%noValue%') {
                $xpath = $this->_getControlXpath('field', $key);
                $searchDataCurrentUser[$key] = $this->getValue($xpath);
            } else {
                $searchDataCurrentUser[$key] = $value;
            }
        }
        $this->navigate('manage_admin_users');
        $this->addParameter('user_first_last_name',
                $searchDataCurrentUser['first_name'] . ' ' . $searchDataCurrentUser['last_name']);
        $this->assertTrue($this->searchAndOpen($searchDataCurrentUser), 'Admin User is not found');
        //Verifying
        $this->clickButtonAndConfirm('delete_user', 'confirmation_for_delete');
        //Verifying
        $this->assertTrue($this->errorMessage('cannot_delete_account'), $this->messages);
    }

}
