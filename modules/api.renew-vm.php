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
$checkAPI = $formApi->validate($_SESSION,$_REQUEST,array("vm_id","days"));
if($checkAPI==true){

  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
  $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
  $vm_id = intval($_REQUEST["vm_id"]);
  $uid = $_SESSION["user_info"]["uid"];
  $checkHasPermissionToThisVM = $db->getNumber("SELECT * FROM vm WHERE vm_id=? AND uid=?",array($vm_id,$uid));
  if($checkHasPermissionToThisVM>=1){
    $vmInfo = $db->select("vm")->find("vm_id","=",$vm_id)->andFind("uid","=",$uid)->execute()[0];
    $packageInfo = $db->select("vm_package")->find("package_id","=",$vmInfo['package_id'])->execute()[0];
    $osvInfo = $db->select("vm_os_version")->find("osv_id","=",$vmInfo["osv_id"])->execute()[0];
    $osInfo = $db->select("vm_os")->find("os_id","=",$osvInfo["os_id"])->execute()[0];
    $userInfo = $db->select("account")->find("uid","=",$uid)->execute()[0];

    $d = $db->decryptText($_REQUEST['days'],"renew");

    if(is_numeric($d)){
      $pricePerMonth = $packageInfo['vm_price'];
      $priceList = doubleval(doubleval($d)*doubleval($pricePerMonth));

      if($priceList <= $userInfo['balance']){
        $totalUserBalance = $userInfo['balance']-$priceList;
        $oldExpTime = $vmInfo["expire"];
        $expTime = strtotime( date("Y-m-d H:i:s", strtotime("+1 month", $oldExpTime)) );
        $updateBalace = $db->query("UPDATE account SET balance=? WHERE uid =?",array($totalUserBalance,$uid));
        $updateExpire = $db->query("UPDATE vm SET expire=? WHERE vm_id=?",array($expTime,$vm_id));
        $return = array("status"=>true,"msg"=>"Successful");
      } else{
        $return = array("status"=>false,"msg"=>"Your balance is not enough");
      }
    } else{ $return = array("status"=>false,"msg"=>"Wrong parameters"); }

  } else{
    $return = array("status"=>false,"msg"=>"You don't have permission");
  }

} else{
  $return = array("status"=>false,"msg"=>"Not found request");
}

if(isset($return) and is_array($return)){echo json_encode($return);}
?>
