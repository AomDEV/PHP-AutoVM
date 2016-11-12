<?php
session_start();
include("../applications/config.php");
require("aom.database.php");
require_once("../applications/class.xen.php");
use aomFramework\database;
use Xen\xen;

if(isset($_SESSION["user_info"]) and $_SESSION["user_info"]["is_login"]==true){
  if(isset($_POST) and isset($_POST["vmId"]) and isset($_POST["token"]) and isset($_POST["state"])){
    if($_POST["token"]==$_SESSION["user_info"]["access_token"]){
      $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
      $checkVM = $db->login("vm",array("uid"=>$_SESSION["user_info"]["uid"],"vm_id"=>$_POST["vmId"]));
      if($checkVM["status"]==true){
        $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
        $vmHostName = $checkVM["info"][0]["hostname"];
				$getVMName = ($xen->VM__get_by_name_label($vmHostName));
        $userInfo = $db->select("account")->find("uid","=",$_SESSION["user_info"]["uid"])->execute();
        $userStatus = $userInfo[0]["status"];
        switch($_POST["state"]){
          case"shutdown":
          if($userStatus==0 or $userStatus==1){
            @$xen->VM__hard_shutdown($getVMName[0]);
            $return = array("status"=>true,"error"=>"success");
          } else{$return = array("status"=>false,"error"=>"access_denied");}
          break;
          case"start":
          if($userStatus==0 or $userStatus==1){
            @$xen->VM__start($getVMName[0],false,true);
            $return = array("status"=>true,"error"=>"success");
          } else{$return = array("status"=>false,"error"=>"access_denied");}
          break;
          case"restart":
          if($userStatus==0 or $userStatus==1){
            @$xen->VM__hard_reboot($getVMName[0]);
            $return = array("status"=>true,"error"=>"success");
          } else{$return = array("status"=>false,"error"=>"access_denied");}
          break;
          case"pause":
          if($userStatus==1){
            @$xen->VM__pause($getVMName[0]);
            $return = array("status"=>true,"error"=>"success");
          } else{$return = array("status"=>false,"error"=>"access_denied");}
          break;
          case"unpause":
          if($userStatus==1){
            @$xen->VM__unpause($getVMName[0]);
            $return = array("status"=>true,"error"=>"success");
          } else{$return = array("status"=>false,"error"=>"access_denied");}
          break;
          default:
          $return = array("status"=>false,"error"=>"not_found_action");
          break;
        }
        echo json_encode($return);
      } else{echo json_encode(array("status"=>false,"error"=>"vm_id"));}
    } else{echo json_encode(array("status"=>false,"error"=>"access_token"));}

  } else{echo json_encode(array("status"=>false,"error"=>"request"));}

} else{
  echo json_encode(array("status"=>false,"error"=>"login"));
}
?>
