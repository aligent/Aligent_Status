<?php

/**
 * Index Controller.  Checks if Magento is operating normally.  Ensure varnish or similar FPC does not cache this route.
 *
 * @category  Aligent
 * @package   Aligent_Status
 * @author    Andrew Dwyer <andrew@aligent.com.au>
 * @copyright 2014 Aligent Consulting.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.aligent.com.au/
 */
class Aligent_Status_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Attempts to communicate with the cache and database to confirm Magento is in an operational state.
     * By the time the request makes it to this point these checks will already have been run by Magento Core, but it
     * doesn't hurt to double check.
     */
    public function indexAction()
    {
        $status = false;
        $oResponse = $this->getResponse();


        try {
            $tableName = Mage::getSingleton('core/resource')->getTableName('core/config_data');
            $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');


            // Check if cache is available and writable
            $result = Mage::app()->getCache()->getBackend()->save(true, get_class($this));
            // Attempt to communicate with the database
            $status = $readConn->isTableExists($tableName) && $writeConn->isTableExists($tableName) && $result;
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if(!$status) {
            $oResponse->setHeader('HTTP/1.0','503',true);
        }

        $oResponse->setBody(Mage::helper('core')->jsonEncode(array('date' => date('c'), "status" => $status)));
        $oResponse->setHeader('Content-type', 'application/json');
    }
}
 