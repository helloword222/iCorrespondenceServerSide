<?php

require_once('nusoap/lib/nusoap.php');
require_once('DAO/inboxDAO.php');

// Pull in the NuSOAP code
// Create the server instance
$server = new soap_server();
// Initialize WSDL support
$server->configureWSDL('MiniCorrespondence', 'urn:correspondencewsdl');

$server->wsdl->addComplexType('inboxCount', 'complexType', 'struct', 'all', '', $inboxCountType);

$server->wsdl->addComplexType('inboxItem', 'complexType', 'struct', 'all', '', $inboxItemType);

$server->wsdl->addComplexType('inboxItems', 'complexType', 'array', '', 'SOAP-ENC:Array', array(), $inboxItemsType, 'tns:inboxItem');
// Register the method to expose

$server->register('getInboxCount',        // method name
    array('userId' => 'xsd:int'),         // input parameters
    array('return' => 'tns:inboxCount'),  // output parameters
    'urn:getInboxCountwsdl',              // namespace
    'urn:getInboxCountwsdl#getInboxCount',// soapaction
    'rpc',                                // style
    'encoded',                            // use
    'get Inbox Count'            		  // documentation
);

$server->register('getNInboxItems',        // method name
    array('userId' => 'xsd:int', 'start' => 'xsd:int', 'length' => 'xsd:int'),         // input parameters
    array('return' => 'tns:inboxItems'),  // output parameters
    'urn:getNInboxItemswsdl',              // namespace
    'urn:getNInboxItemswsdl#getNInboxItems',// soapaction
    'rpc',                                // style
    'encoded',                            // use
    'get N Inbox Items'            		  // documentation
);
// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>