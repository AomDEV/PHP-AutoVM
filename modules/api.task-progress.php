<?php
session_start();
include("../applications/config.php");
require("aom.database.php");
require("aom.form.php");
require_once("../applications/class.xen.php");
use aomFramework\database;
use aomFramework\api;
use Xen\xen;

$formApi = new api();
$checkAPI = $formApi->validate($_SESSION,$_POST,array("vmId"));
if($checkAPI==true){
    $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
    $vmId = intval($_POST["vm_id"]);
    $checkVM = $db->login("vm",array("uid"=>$_SESSION["user_info"]["uid"],"vm_id"=>$vmId));
    if($checkVM['status']==true){
      $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
      $vmHostName = $checkVM["info"][0]["hostname"];
      $xen->setVMID($vmHostName);

      $vm = $xen->getVMID();
      if($xen->task__get_status($task) == "pending"){
          $progress = $xen->task__get_progress($task);
      }
      $xen->task__destroy($task);
      echo json_encode( array("status"=>true,"progress"=>$progress) );
    }
}
?>
