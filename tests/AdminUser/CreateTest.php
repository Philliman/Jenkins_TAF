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
 * Creating Admin User
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AdminUser_CreateTest extends Mage_Selenium_TestCase
{

    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     * <p>Navigate to System -> Permissions -> Users./p>
     * @group dev
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_admin_users');
        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);
        $this->addParameter('id', '0');
    }

    /**
     * <p>Test navigation.</p>
     * <p>Steps:</p>
     * <p>1. Verify that 'Add New User' button is present and click her.</p>
     * <p>2. Verify that the create user page is opened.</p>
     * <p>3. Verify that 'Back' button is present.</p>
     * <p>4. Verify that 'Save User' button is present.</p>
     * <p>5. Verify that 'Reset' button is present.</p>
     * 
     * @group dev
     */
    public function test_Navigation()
    {
        $this->assertTrue($this->buttonIsPresent('add_new_admin_user'),
                'There is no "Add New Customer" button on the page');
        $this->clickButton('add_new_admin_user');
        $this->assertTrue($this->checkCurrentPage('new_admin_user'), $this->messages);
        $this->assertTrue($this->buttonIsPresent('back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_admin_user'), 'There is no "Save User" button on the page');
        $this->assertTrue($this->buttonIsPresent('reset'), 'There is no "Reset" button on the page');
    }

    /**
     * <p>Create Admin User (all required fields are filled).</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields.</p>
     * <p>4.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user successfully saved.</p>
     * <p>Message "The user has been saved." is displayed.</p>
     *
     * @depends test_Navigation
     * @group dev
     */
    public function test_WithRequiredFieldsOnly()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', Null, array('email', 'user_name'));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);

        return $userData;
    }

    /**
     * <p>Create Admin User. Use user name that already exist</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New User' button.</p>
     * <p>2. Fill in 'user name' field by using data that already exist.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save User' button.</p>
     * <p>Expected result:</p>
     * <p>User is not created. Error Message is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @param array $userData
     */
    public function test_WithUserNameThatAlreadyExists($userData)
    {
        //Data
        $userData['email'] = $this->generate('email', 20, 'valid');
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->errorMessage('exist_name_or_email'), $this->messages);
    }

    /**
     * <p>Create Admin User. Use email that already exist</p>
     * <p>Steps:</p>
     * <p>1. Click 'Add New User' button.</p>
     * <p>2. Fill in 'email' field by using email that already exist.</p>
     * <p>3. Fill other required fields by regular data.</p>
     * <p>4. Click 'Save User' button.</p>
     * <p>Expected result:</p>
     * <p>User is not created. Error Message is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @param array $userData
     */
    public function test_WithUserEmailThatAlreadyExists($userData)
    {
        //Data
        $userData['user_name'] = $this->generate('string', 5, ':lower:');
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->errorMessage('exist_name_or_email'), $this->messages);
    }

    /**
     * <p>Create Admin User with one empty reqired field.</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill fields exept one required.</p>
     * <p>4.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user is not saved.</p>
     * <p>Message "This is a required field." is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @dataProvider data_emptyFields
     */
    public function test_WithRequiredFieldsEmpty($emptyField, $messageCount)
    {

        $userData = $this->loadData('generic_admin_user', array($emptyField => '%noValue%'),
                array('email', 'user_name'));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $xpath = $this->_getControlXpath('field', $emptyField);
        $this->addParameter('fieldXpath', $xpath);
        $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount($messageCount), $this->messages);
    }

    public function data_emptyFields()
    {
        return array(
            array('user_name', 1),
            array('first_name', 1),
            array('last_name', 1),
            array('email', 1),
            array('password', 2),
            array('password_confirmation', 1),
        );
    }

    /**
     * <p>Create Admin User (all required fields are filled by special chracters).</p>
     * <p>Steps:</p>
     * <p>1.Press "Add New User" button.</p>
     * <p>2.Fill in all required fields by special chracters
     * (exept 'email', 'password' and 'password_confirmation' fields).</p>
     * <p>3.Fill in 'email', 'password' and 'password_confirmation' fields by valid data.</p>
     * <p>4.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user is saved.</p>
     * <p>Message "The user has been saved." is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @group dev
     */
    //this test will be failed occasionally, caused by ? character, need to find a solution for it. 
//    public function test_WithSpecialCharacters_exeptEmail()
//    {
//        //Data
//        $specialCharacters = array(
//            'user_name'  => $this->generate('string', 32, ':punct:'),
//            'first_name' => $this->generate('string', 32, ':punct:'),
//            'last_name'  => $this->generate('string', 32, ':punct:'),
//        );
//        $userData = $this->loadData('generic_admin_user', $specialCharacters, 'email');
//        $searchData = $this->loadData('search_admin_user',
//                array('email' => $userData['email'], 'user_name' => $userData['user_name'],
//                    'first_name' => $userData['first_name'], 'last_name' => $userData['last_name']));
//        //Steps
//        $this->adminUserHelper()->createAdminUser($userData);
//        //Verifying
//        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
//        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);
//        //Searching
//        $this->addParameter('user_first_last_name',
//                $searchData['first_name'] . ' ' . $searchData['last_name']);
//        $this->assertTrue($this->searchAndOpen($searchData), 'Admin User is not found');
//        //Steps
//        $this->assertTrue(
//                $this->verifyForm(
//                        $userData, 'user_info', array('password', 'password_confirmation')
//                ), $this->messages);
//        
//        $this->clickButtonAndConfirm('delete_user', 'confirmation_for_delete');
//        //Verifying
//        $this->assertTrue($this->successMessage('success_deleted_user'), $this->messages);
//    }
     
    
    /**
     * <p>Create Admin User (all required fields are filled by long value data).</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields by long value data (exclude 'email').</p>
     * <p>4.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user is not saved.</p>
     * <p>Message "The user has been saved." is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @group dev
     */
    
    
    public function test_WithLongValues()
    {
        //Data
        $password = $this->generate('string', 255, ':alnum:');
        $longValues = array(
            'user_name'             => $this->generate('string', 40, ':alnum:'),
            'first_name'            => $this->generate('string', 32, ':alnum:'),
            'last_name'             => $this->generate('string', 32, ':alnum:'),
            'email'                 => $this->generate('email', 128, 'valid'),
            'password'              => $password,
            'password_confirmation' => $password
        );
        $userData = $this->loadData('generic_admin_user', $longValues);
        $searchData = $this->loadData('search_admin_user',
                array('email' => $userData['email'], 'user_name' => $userData['user_name'],
                    'first_name' => $userData['first_name'], 'last_name' => $userData['last_name']));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);
        //Searching
        $this->addParameter('user_first_last_name',
                $searchData['first_name'] . ' ' . $searchData['last_name']);
        $this->assertTrue($this->searchAndOpen($searchData), 'Admin User is not found');
        //Verifying the data saved match the expectation 
        $this->assertTrue(
                $this->verifyForm(
                        $userData, 'user_info', array('password', 'password_confirmation')
                ), $this->messages);
        //Delete the created user
        $this->clickButtonAndConfirm('delete_user', 'confirmation_for_delete');
        //Verify the user was deleted successfully
        $this->assertTrue($this->successMessage('success_deleted_user'), $this->messages);
    }

    
    /**
     * <p>Create Admin User. Use wrong values for 'password' fields.</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields by regular data (exclude 'Password' and 'Password Confirmation').</p>
     * <p>4.Fill 'Password' and 'Password Confirmation' by wrong values.</p>
     * <p>5.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user is not saved.</p>
     * <p>Error Message is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @dataProvider data_invalidPassword
     */
    public function test_WithInvalidPassword($wrongPasswords, $errorMessage)
    {
        //Data
        $userData = $this->loadData('generic_admin_user', $wrongPasswords, array('email', 'user_name'));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->errorMessage($errorMessage), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function data_invalidPassword()
    {
        return array(
            array(array(
                    'password' => '1234567890',
                    'password_confirmation' => '1234567890',
                ), 'invalid_password'),
            array(array(
                    'password' => 'qwertyqw',
                    'password_confirmation' => 'qwertyqw',
                ), 'invalid_password'),
            array(array(
                    'password' => '123qwe',
                    'password_confirmation' => '123qwe',
                ), 'invalid_password'),
            array(array(
                    'password' => '123123qwe',
                    'password_confirmation' => '1231234qwe',
                ), 'password_unmatch')
        );
    }

    /**
     * <p>Create Admin User (with invalid data in the 'email' field).</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields by regular data (exclude 'email').</p>
     * <p>4.Fill 'email' field by invalid data [example: me&you@domain.com / me&you@com / nothing@домен.com].</p>
     * <p>5.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user is not saved.</p>
     * <p>Message "Please enter a valid email." OR "Please enter a valid email address.
     * For example johndoe@domain.com." is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     * @dataProvider data_InvalidEmail
     */
    public function test_WithInvalidEmail($invalidEmail)
    {
        //Data
        $userData = $this->loadData('generic_admin_user', array('email' => $invalidEmail), 'user_name');
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->errorMessage('invalid_email'), $this->messages);
    }

    public function data_InvalidEmail()
    {
        return array(
            array('invalid'),
            array('test@invalidDomain'),
            array('te@st@magento.com')
        );
    }

    /**
     * <p>Create Admin User  (as Inactive).</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields.</p>
     * <p>4.Choose in the 'This account is' dropdown - "Inactive".</p>
     * <p>5.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user successfully saved. Message "The user has been saved." is displayed.</p>
     * <p>6.Log out</p>
     * <p>7.Log in using created user.</p>
     * <p>Expected result:</p>
     * <p>Error Message "This account is inactive." is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_InactiveUser()
    {
        //Data
        $userData = $this->loadData('generic_admin_user',
                array('this_acount_is' => 'Inactive', 'role_name' => 'Administrators'), array('email', 'user_name')
        );
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->loginAdmin($userData);
        //Verifying
        $this->assertTrue($this->errorMessage('inactive_account'), $this->messages);
    }

    /**
     * <p>Create Admin User (with Admin User Role).</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields.</p>
     * <p>4.Choose in the 'User Role' grid - "Administrators" role.</p>
     * <p>5.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user successfully saved. Message "The user has been saved." is displayed</p>
     * <p>6.Log out</p>
     * <p>7.Log in using created user.</p>
     * <p>Expected result:</p>
     * <p>Logged in to Admin.</p>.
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithRole()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', array('role_name' => 'Administrators'),
                array('email', 'user_name'));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->loginAdmin($userData);
        //Verifying
        $this->assertTrue($this->checkCurrentPage('dashboard'), $this->messages);
    }

    /**
     * <p>Create Admin User (with Admin User Role).</p>
     * <p>Steps:</p>
     * <p>1.Go to System-Permissions-Users.</p>
     * <p>2.Press "Add New User" button.</p>
     * <p>3.Fill all required fields.</p>
     * <p>4.Press "Save User" button.</p>
     * <p>Expected result:</p>
     * <p>New user successfully saved. Message "The user has been saved." is displayed</p>
     * <p>6.Log out</p>
     * <p>7.Log in using created user.</p>
     * <p>Expected result:</p>
     * <p>Error Message "Access denied." is displayed.</p>
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithoutRole()
    {
        //Data
        $userData = $this->loadData('generic_admin_user', NULL, array('email', 'user_name'));
        //Steps
        $this->adminUserHelper()->createAdminUser($userData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_user'), $this->messages);
        //Steps
        $this->logoutAdminUser();
        $this->adminUserHelper()->loginAdmin($userData);
        //Verifying
        $this->assertTrue($this->errorMessage('access_denied'), $this->messages);
    }

}
