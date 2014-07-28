<?php
class RegressionCreateOrders extends Mage_Selenium_TestCase
{
  /**
     * <p>Create order depends on order data.</p>
     * <p>Steps:</p>
     * <p>1.navigate to frontend.</p>
     * <p>2.Add products to cart.</p>
     * <p>3.checkout page and place the order.</p>
     * <p>4.verify the order created as expected.</p>
     * <p>Expected result:</p>
     * <p>Order Created.</p>
     *      */
    public function test_CreateOrders()
    {
    
        //navigate to frontend home page 
        $this->frontend();
        //load order data
        $orderData = $this->loadData('order_testCustmoer_savedcc'); 
        $orderData['payment_data']['payment_info'] = $this->loadData('saved_visa');
        
        //create order according to orderdata
        $this->regressionHelper()->createOrder($orderData);
        
        //Verifying
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
  }
}
?>