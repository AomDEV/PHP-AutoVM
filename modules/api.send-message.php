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

$formApi = new api();
$form = new form();
$checkAPI = $formApi->validate($_SESSION,$_REQUEST,array("message","conversation_id"));
if($checkAPI==true){
  $db = new database($config["host"],$config["user"],$config["pass"],$config["db_name"]);
  $conversation_id = intval($db->decryptText($_REQUEST["conversation_id"],'conversation'));
  $msg = urldecode(htmlentities($_REQUEST['message']));
  $uid = $_SESSION["user_info"]["uid"];
  $checkConversationID = $db->getNumber("SELECT * FROM ticket WHERE conversation_id=? AND uid=?",array($conversation_id,$uid));
  if($checkConversationID>=1){
    $insert = $db->query("INSERT INTO ticket_conversation (cid,message,conversation_id,uid,time,has_read) VALUES (NULL,?,?,?,?,?);",array($msg,$conversation_id,$uid,time(),false));
    $return = array("status"=>true,"msg"=>"Successful","result"=>$insert);
  } else{
    $return = array("status"=>false,"msg"=>"Not found VM Info");
  }
} else{
  $return = array("status"=>false,"msg"=>"Not found Request");
}

if(isset($return) and is_array($return)){
  $jsonEncode = json_encode($return);
  print($jsonEncode);
}
?>
