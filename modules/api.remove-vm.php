<?php
# Pattern Template file : [plan_name].[hdd]-[os_name][osv_id]
# Pattern Example : starter.102400-windows1
session_start();
include("../applications/config.php");
require("aom.error.php");
require("aom.database.php");
include_once('../applications/AES.php');
require("aom.form.php");
require_once("../applications/class.xen.php");
use aomFramework\database;
use aomFramework\api;
use aomFramework\form;
use Xen\xen;

$_TESTMODE = false;
$_SAVE_ADMIN_USER_LOG = false;

$formApi = new api();
$form = new form();
$checkAPI = $formApi->validate($_SESSION,$_REQUEST,array("vm_id"));
if($checkAPI==true and is_numeric($_REQUEST["vm_id"])){
  $vm_id = intval($_REQUEST["vm_id"]);
  $uid = $_SESSION["user_info"]["uid"];
  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
  $getVMSQL = "SELECT hostname FROM vm WHERE vm_id=? AND uid=?";
  $checkVM = $db->getNumber($getVMSQL,array($vm_id,$uid));
  if($checkVM==1){
    $getVM = $db->getRow($getVMSQL,array($vm_id,$uid));
    $getHostname = $getVM["hostname"];

    # Remove VM in Xen
    $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
    $xen->setVMID($getHostname);
    if($xen->getPowerState() == "Halted"){
      $xen->destroy();
      # Remove VM in Database
      $db->query("DELETE FROM vm WHERE vm_id=? AND uid=?",array($vm_id,$uid));
      $return = array("status"=>true,"msg"=>"Successful");
    } else{
      $return = array("status"=>false,"msg"=>"Please shutdown VM before do this process");
    }
  } else{
    $return = array("status"=>false,"msg"=>"Not found VM");
  }
} else{
  $return = array("status"=>false,"msg"=>"Not found request");
}

if(isset($return) and is_array($return)){
  $jsonEncode = json_encode($return);
  print($jsonEncode);
}
?>
