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
$checkAPI = $formApi->validate($_SESSION,$_POST,array("os","osv","plan","hostname"));
if($checkAPI==true){
  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);

  $os = intval($_POST["os"]);
  $osv = intval($_POST["osv"]);
  $plan = intval($_POST["plan"]);
  $hostname = (string)$_POST["hostname"];
  if(preg_match("/^[a-z0-9]*\.[a-z0-9]*\.[a-z]{2,4}/",$hostname)){
    $uid = $_SESSION["user_info"]["uid"];
    $getUserInfo = $db->getRow("SELECT balance FROM account WHERE uid=?",array($uid));
    $checkVMOS = $db->getNumber("SELECT * FROM vm_os_version WHERE osv_id=? AND os_id=?",array($osv,$os));
    $getPackage = $db->getRow("SELECT vm_price,package_name,vm_disk,vm_core,vm_ram FROM vm_package WHERE package_id=?",array($plan));
    $getOS = $db->getRow("SELECT os_code,os_id FROM vm_os WHERE os_id=?",array($os));
    $checkPlan = $db->getNumber("SELECT * FROM vm_package WHERE package_id=?",array($plan));
    $checkHostname = $db->getNumber("SELECT * FROM vm WHERE hostname=?",array($hostname));
    if($checkHostname>0){
      $return = array("msg"=>"Hostname is already exist","status"=>false);
    } else if($checkVMOS<=0 and $checkPlan<=0){
      $return = array("msg"=>"Not found OS or Plan","status"=>false);
    } else if($getUserInfo["balance"]<$getPackage["vm_price"]){
      $return = array("msg"=>"Your balance is not enough","status"=>false);
    } else{
      # Create VM
      $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
      $cpu = $getPackage["vm_core"];
      $memory = $getPackage["vm_ram"];
      $templateName = strtolower($getPackage["package_name"]).".{$getPackage["vm_disk"]}-{$getOS["os_code"]}{$os}";
      $createVM = $xen->createVM($templateName,$hostname,$cpu,$memory);
      if($createVM["status"]==false){
        $return = array("msg"=>$createVM["msg"],"status"=>false);
      } else{
        $newVM = $createVM["vm"];

        # Update user balance
        $packageBalance = $getPackage["vm_price"];
        $totalUserBalance = $getUserInfo["balance"]-$packageBalance;
        if($_TESTMODE==false){
          $updateBalace = $db->query("UPDATE account SET balance=? WHERE uid =?",array($totalUserBalance,$uid));
        }

        # Add to database
        if($_TESTMODE==false){
          $vmIP = "null";
          $transaction = $form->generateTXID();
          $expire_date = strtotime( date('Y-m-d H:i:s', strtotime( '+30 day', time() )) );
          $db->query("INSERT INTO vm (vm_id,uid,hostname,username,password,ip,memory,disk,region,osv_id,core,status,expire,transaction,package_id,last_create) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);",
          array( NULL,$uid,$hostname,"null","null",$vmIP,$memory,$getPackage["vm_disk"],1,$getOS["os_id"],$cpu,0,$expire_date,$transaction,$plan,time() ));
        }
        # Add to database

        # Insert log data
        if($_TESTMODE==false){
          if($_SAVE_ADMIN_USER_LOG==false and $_SESSION["user_info"]["uid"]==1){
            # Nothing
          } else{
            $logName = "create:vm.{$os}-{$osv}-{$plan}";
            $logAction = "result:success";
            $logReturn = $db->encryptText(json_encode(array($newVM)),"log_return");
            $logIP = $_SERVER["REMOTE_ADDR"];
            $insertLog = $db->query("INSERT INTO a_payment_log (log_id,log_name,log_action,log_return,log_ip,log_uid,log_time) VALUES (?,?,?,?,?,?,?)",array(NULL,$logName,$logAction,$logReturn,$logIP,$uid,time()));
          }
        }

        # Return value to client
        $getNewVM = $db->select("vm")->find("hostname","=",$hostname)->execute()[0];
        $return = array("msg"=>"Successful!","status"=>true,"vm_id"=>$getNewVM["vm_id"]);
      }
      #===============
      # Create VM
    }
  } else{
    $return = array("msg"=>"Not found request","status"=>false);
  }
  print( json_encode($return) );
} else{
  print(json_encode(array("msg"=>"Not found request","status"=>false)));
}
?>
