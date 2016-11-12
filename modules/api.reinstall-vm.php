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
$checkAPI = $formApi->validate($_SESSION,$_POST,array("vm_id"));
if($checkAPI==true){
  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);

  $vm_id = intval($_POST["vm_id"]);
  if(is_numeric($vm_id)){
    $uid = $_SESSION["user_info"]["uid"];
    $getUserInfo = $db->getRow("SELECT * FROM account WHERE uid=?",array($uid));
    $checkVMIsMine = $db->getNumber("SELECT * FROM vm WHERE vm_id=? AND uid=?",array($vm_id,$uid));
    if($checkVMIsMine==1){
      $getVMInfo = $db->getRow("SELECT * FROM vm WHERE vm_id=? AND uid=?",array($vm_id,$uid));
      $getPlanInfo = $db->getRow("SELECT * FROM vm_package WHERE package_id=?",array($getVMInfo['package_id']));
      $getOSVInfo = $db->getRow("SELECT * FROM vm_os_version WHERE osv_id=?",array($getVMInfo['osv_id']));
      $getOSInfo = $db->getRow("SELECT * FROM vm_os WHERE os_id=?",array($getOSVInfo['os_id']));
      $templateName = strtolower($getPlanInfo["package_name"]).".{$getPlanInfo["vm_disk"]}-{$getOSInfo["os_code"]}{$getOSVInfo['os_id']}";
      $new_hostname = $getVMInfo["hostname"];
      if($getUserInfo['balance'] >= $ReInstallVMFee){
        $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
        $xen->setVMID($new_hostname);
        if($xen->getPowerState() == "Halted"){
          # Remove VM
          $xen->destroy();
          # Create VM while it's success!
          $statusCreate = false;
          $createResult = null;
          while($statusCreate == false){
            # Create VM
            $createVM = $xen->createVM($templateName,$new_hostname,$getVMInfo['core'],$getVMInfo['memory']);
            $statusCreate = $createVM['status'];
            $createResult = $createVM;
          }

          if($statusCreate==true){
            if($createResult["status"]==false){
              $return = array("msg"=>$createResult["msg"],"status"=>false);
            } else{
              # Edit user balance
              $totalUserBalance = $getUserInfo["balance"]-$ReInstallVMFee;
              $updateBalace = $db->query("UPDATE account SET balance=? WHERE uid =?",array($totalUserBalance,$uid));
              # Edit VM Database Info to support new VM
              $db->query("UPDATE vm SET ip=?,ip_updated=?,last_create=? WHERE vm_id=?;",array( "null" , 0 , time() , $vm_id ));
              $return = array("msg"=>"success","status"=>true);
            }
          }
        } else{
          $return = array("msg"=>"Please shutdown VM before do this process","status"=>false);
        }
      } else{
        $return = array("msg"=>"Your balance is not enough","status"=>false);
      }
    } else{
      $return = array("msg"=>"You don't have permission","status"=>false);
    }
  } else{
    $return = array("msg"=>"Not found request","status"=>false);
  }
  print( json_encode($return) );
} else{
  print(json_encode(array("msg"=>"Not found request","status"=>false)));
}
?>
