<?php
session_start();
include("../applications/config.php");
require("aom.database.php");
require("aom.form.php");
require_once('../libraries/http_client/vendor/autoload.php');
require_once("../applications/class.xen.php");
use aomFramework\database;
use aomFramework\api;
use Xen\xen;

$formApi = new api();
$http = $_REQUEST;
$checkAPI = $formApi->validate($_SESSION,$http,array("vmId"));
if($checkAPI==true){
  $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
  $xen->setVMID("vps.9cloud.com");
  $vmId = $xen->getVMID();
  if( $xen->getPowerState() == "Running" ){
    $allowView = array("memory","cpu","disk","*");
    if(isset($http["view"]) && in_array($http["view"],$allowView)){
      $view = $http["view"];
      $memory = ($view=="*" || $view=="memory") ? $xen->getMemoryUsage() : 0;
      $cpu = ($view=="*" || $view=="cpu") ? $xen->getCPUUsage() : 0;
      $disk = ($view=="*" || $view=="disk") ? $xen->getDiskUsage() : 0;

      $array = array("memory"=>$memory,"cpu"=>$cpu,"disk"=>$disk);
      echo json_encode(array("status"=>true,"overview"=>$array,"msg"=>"Succeed"));
    } else{
      echo json_encode(array("status"=>false,"error"=>"undefined_view","msg"=>"Not found view!"));
    }
  } else{
    echo json_encode(array("status"=>false,"error"=>"vm_offline","msg"=>"This Machine is offline!"));
  }
} else{
  echo json_encode(array("status"=>false,"error"=>"request","msg"=>"Not found request!"));
}
