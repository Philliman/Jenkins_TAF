<?php
class AdminUser_DeleteAdminUsers extends Mage_Selenium_TestCase
{
  public function test_DeleteAdminUsersCreatedDuringTest()
  {
    $isUserPresent= true;
    $numDeletedUser = 0;
    //login to Admin
    $this->loginAdminUser();
    
    //Loop to delete those users created during the test with name Johndoe in email
    while($isUserPresent){
        $this->navigate('manage_admin_users');
        $this->assertTrue($this->checkCurrentPage('manage_admin_users'), $this->messages);
        $this->assertEquals("Users / Permissions / System / Magento Admin", $this->getTitle());
        $this->type("id=permissionsUserGrid_filter_email", "johndoe");
        $this->clickButton('search', false);
        $this->waitForAjax();
    
        if(!$this->isElementPresent("css=td.empty-text.a-center") && $this->isElementPresent("xpath=//td[@class='a-right ']")){
            $this->click("xpath=//td[@class='a-right ']");
            $this->waitForPageToLoad(30000);
            //$this->assertTrue($this->checkCurrentPage('edit_admin_user'), $this->messages);
//            $this->validatePage("edit_admin_user");
            $emailValue = $this->getValue("id=user_email");
            echo $emailValue +"\n";
            if(strpos($this->getValue("id=user_email"), 'johndoe@domain.com')!==false){
                $this->click("//button[span='Delete User']", false);
                $this->waitForConfirmationPresent();
                $this->waitForPageToLoad(30000);
                $this->assertTrue($this->successMessage('success_deleted_user'), $this->messages);
                $numDeletedUser ++;
                }
        }
        else{
            if($numDeletedUser==0)
                echo "No user been deleted. \n";
            else
                echo $numDeletedUser ." users have been delected. \n";
            $isUserPresent = false;
        }
    }
    $this->captureEntirePageScreenshot("C:\\SeleniumIDE\\Temp\\screentshot.png", "");
  }
}
?>