<?php
require_once ('apiBase.class.php');
require_once ('apiRequest.class.php');
require_once ('apiDB.class.php');
require_once ('apiUtility.class.php');
date_default_timezone_set('UTC');
error_reporting(0);

/**
 * ESEA REST API for GETing team rankings/results and POSTing match results, maps, and teams
 */
$api = new apiRequest();
$api->output();