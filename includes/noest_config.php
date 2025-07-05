<?php
// Noest API Configuration
define('NOEST_API_TOKEN', 'ThV0XvsQiucTQoGI9haxOLjs9B2CGztH4nE');
define('NOEST_USER_GUID', 'EGBYMZK9');

// Load Noest API class
require_once 'noest_api.php';

// Initialize Noest API
$noestAPI = new NoestAPI(NOEST_API_TOKEN, NOEST_USER_GUID);
