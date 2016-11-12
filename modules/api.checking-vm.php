<?php
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

$_DEBUG = true;
$formApi = new api();
$checkAPI = $formApi->validate($_SESSION,$_REQUEST,array("vm_id"));
if($checkAPI==true){

  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
  $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
  $vm_id = intval($_REQUEST["vm_id"]);
  $vmInfo = $db->select("vm")->find("vm_id","=",$vm_id)->execute()[0];
  $osvInfo = $db->select("vm_os_version")->find("osv_id","=",$vmInfo["osv_id"])->execute()[0];
  $osInfo = $db->select("vm_os")->find("os_id","=",$osvInfo["os_id"])->execute()[0];

  $getVMIP = $vmInfo["ip"];

  if($vmInfo["ip_updated"]==false){
    $xen->setVMID($vmInfo["hostname"]);
    $ipRef = $xen->getGuestMetrics();
    if(isset($ipRef['networks']['0/ip'])){
      $vmIP = $ipRef['networks']['0/ip'];
      if(!filter_var($vmIP, FILTER_VALIDATE_IP) === false){
        if($getVMIP!=$vmIP){
          $updateArrayIP = array("ip"=>$vmIP,"ip_updated"=>true);
          $getVMIP = $vmIP;
        } else{
          $updateArrayIP = array("ip_updated"=>true);
        }
        $db->update("vm",$updateArrayIP,"vm_id","=",$_REQUEST["vm_id"]);
      } else{
        $return = array("status"=>true,"force_end"=>true,"msg"=>"Successful","ip"=>$vmIP);
      }
    } else{
      $return = array("status"=>false,"msg"=>"Not found VM IP");
    }
  } else{
    $return = array("status"=>true,"msg"=>"Ready to use","ip"=>$getVMIP);
  }

} else{
  $return = array("status"=>false,"msg"=>"Not found request");
}

if(isset($return) and is_array($return)){echo json_encode($return);}
?>
