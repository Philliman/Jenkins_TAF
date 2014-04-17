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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrderShipment_Helper extends Mage_Selenium_TestCase
{

    /**
     * Provides partial or fill shipment
     *
     * @param array $shipmentData
     */
    public function createShipmentAndVerifyProductQty(array $shipmentData = array())
    {
        $shipmentData = $this->arrayEmptyClear($shipmentData);
        $verify = array();

        $this->clickButton('ship');
        foreach ($shipmentData as $product => $options) {
            if (is_array($options)) {
                $sku = (isset($options['ship_product_sku'])) ? $options['ship_product_sku'] : NULL;
                $productQty = (isset($options['ship_product_qty'])) ? $options['ship_product_qty'] : '%noValue%';
                if ($sku) {
                    $verify[$sku] = $productQty;
                    $this->addParameter('sku', $sku);
                    $this->fillForm(array('qty_to_ship' => $productQty));
                }
            }
        }
        if (!$verify) {
            $setXpath = $this->_getControlXpath('fieldset', 'product_line_to_ship');
            $skuXpath = $this->_getControlXpath('field', 'product_sku');
            $qtyXpath = $this->_getControlXpath('field', 'product_qty');
            $productCount = $this->getXpathCount($setXpath);
            for ($i = 1; $i <= $productCount; $i++) {
                $prod_sku = $this->getText($setXpath . "[$i]" . $skuXpath);
                $prod_sku = trim(preg_replace('/SKU:|\\n/', '', $prod_sku));
                $prod_qty = $this->getAttribute($setXpath . "[$i]" . $qtyXpath . '/@value');
                $verify[$prod_sku] = $prod_qty;
            }
        }
        $this->clickButton('submit_shipment');
        $this->assertTrue($this->successMessage('success_creating_shipment'), $this->messages);
        foreach ($verify as $productSku => $qty) {
            if ($qty == '%noValue%') {
                continue;
            }
            $this->addParameter('sku', $productSku);
            $this->addParameter('shippedQty', $qty);
            $xpathShiped = $this->_getControlXpath('field', 'qty_shipped');
            $this->assertTrue($this->isElementPresent($xpathShiped),
                    'Qty of shipped products is incorrect at the orders form');
        }
    }

}
