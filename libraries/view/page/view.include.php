<?php
include("applications/config.php");
include("applications/email.text.php");
require_once("modules/aom.autoload.php");
include_once('applications/AES.php');

use aomFramework\database;
use aomFramework\frontend;
use aomFramework\form;
use aomFramework\error;
use aomFramework\viewer;

$frontend = new frontend();
$form = new form();
$er = new error();
$db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
?>
