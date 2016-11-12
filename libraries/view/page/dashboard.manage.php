<?php
## Load Function and Use Namespace ##
require_once('libraries/http_client/vendor/autoload.php');
include_once('applications/AES.php');
require_once("applications/class.xen.php");
use Xen\xen;
$frontend->load("assets/js/modal.js");
## Load Function and Use Namespace ##

$checkVM = $db->login("vm",array("uid"=>$form->get_session($_SESSION,"uid"),"vm_id"=>$_REQUEST["vm_id"]));
if($checkVM["status"]==true){
  $vmInfo = $db->select("vm")->find("vm_id","=",$_REQUEST["vm_id"])->execute()[0];
  $xen = new xen($config["xen_ip"],$config["xen_user"],$config["xen_pass"]);
  $vmHostName = $vmInfo["hostname"];
  $xen->setVMID($vmHostName);
  $getVMName = $xen->getVMID();
  $getMetrics = $xen->getMetrics();
  $getGuestMetrics = $xen->getGuestMetrics();
  $osvInfo = $db->select("vm_os_version")->find("osv_id","=",$vmInfo["osv_id"])->execute()[0];
  $osInfo = $db->select("vm_os")->find("os_id","=",$osvInfo["os_id"])->execute()[0];
  $getVMIP = $vmInfo["ip"];
  $uid = $form->get_session($_SESSION,"uid");

  if($vmInfo["ip_updated"]==false){
    $ipRef = @$xen->getGuestMetrics();
    $vmIP = @$ipRef['networks']['0/ip'];
    if(!filter_var($vmIP, FILTER_VALIDATE_IP) === false){
      if($getVMIP!=$vmIP){
        $updateArrayIP = array("ip"=>$vmIP,"ip_updated"=>true);
        $getVMIP = $vmIP;
      } else{
        $updateArrayIP = array("ip_updated"=>true);
      }
      $db->update("vm",$updateArrayIP,"vm_id","=",$_GET["vm_id"]);
    } else{
      echo '<div class="alert alert-info"><i class="fa fa-cog fa-spin"></i> ';
      echo 'This VM is now setting up, Please try again later.';
      echo '</div>';
      exit();
    }
  }

  echo '<script>var my_vmId = '.intval($_GET["vm_id"]).';var my_userToken = "'.$_SESSION["user_info"]["access_token"].'";</script>';

  echo '<div class="ui top attached tabular menu">';
  echo '<a class="item active" data-tab="summary"><i class="dashboard icon"></i> Summary</a>';
  echo '<a class="item" data-tab="power"><i class="power icon"></i> Power Control</a>';
  # Disabled Function
  #echo '<a class="item" data-tab="console"><i class="desktop icon"></i> Console</a>';
  echo '<a class="item" data-tab="other"><i class="setting icon"></i> Other</a>';
  echo '</div>';

  echo '<!--Summary-->';
  echo '<div class="ui bottom attached tab segment active" data-tab="summary">';
  echo "<h1><i class='fa fa-".$osInfo["os_product"]."'></i> ".$getVMIP." <font style='font-size:16px;color:#777;'>".$vmInfo["hostname"]."</font></h1>";
  echo '<div class="table-responsive fluid" style="margin-left:-14px;margin-right:-14px;margin-bottom:-25px;">';
  echo '<table class="table table-hover">';
  function progress_bar($percent,$id=null,$text=null,$color=null){
    if($text==null){$text = $percent."%";}
    if($color==null){$color = "success";}
    $html = '<div class="ui progress" style="margin:0px;">';
    $html.= '<div class="progress-bar usableReview progress-bar-'.$color.' progress-bar-striped active" aria-valuemin="0" aria-valuemax="100" data-bar="'.$id.'" style="width: '.$percent.'%;">'.$text.'</div>';
    $html.= '</div>';
    return $html;
  }

  $cpuPercentage = 100;
  $diskPercentage = 100;
  $memPercentage = 100;

  echo '<tr> <th width="120">CPU Usage</th> <td> '.progress_bar($cpuPercentage,"cpuUsage").' </td> </tr>';
  echo '<tr> <th>Memory Usage</th> <td> '.progress_bar($memPercentage,"memUsage").' </td> </tr>';
  echo '<tr> <th>Disk Usage</th> <td> '.progress_bar($diskPercentage,"diskUsage","0 / 0 KBs","info").' </td> </tr>';
  echo '</table>';
  echo '</div>';
  echo '<h3><i class="cubes icon"></i> Your Package</h3>';
  echo '<div class="table-responsive fluid" style="margin-left:-14px;margin-right:-14px;margin-bottom:-30px;">';
  echo '<table class="table table-hover">';
  if($vmInfo["core"]==1){$prefixCore="Core";}else{$prefixCore="Cores";}
  echo '<tr> <th width="70">Core</th> <td><i class="cube icon"></i> <b>'.$vmInfo["core"].'</b> '.$prefixCore.'</td> </tr>';
  if($vmInfo["memory"]<1024){$ram=$vmInfo["memory"];$prefixRam="MB";}else{$ram=($vmInfo["memory"])/1024;$prefixRam="GB";}
  echo '<tr> <th>Ram</th> <td><i class="server icon"></i> <b>'.number_format($ram,0).'</b> '.$prefixRam.' <span class="text-muted">('.number_format($vmInfo["memory"],0).'MB)</span></td> </tr>';
  if($vmInfo["disk"]<1024){$hdd=$vmInfo["disk"];$prefixHDD="MB";}else{$hdd=($vmInfo["disk"])/1024;$prefixHDD="GB";}
  echo '<tr> <th>HDD</th> <td><i class="database icon"></i> <b>'.number_format($hdd,0).'</b> '.$prefixHDD.' <span class="text-muted">('.number_format($vmInfo["disk"],0).'MB)</span></td> </tr>';
  echo '</table>';
  echo '</div>';
  echo '</div>';

  echo '<!--Power Control-->';
  echo '<div class="ui bottom attached tab segment" data-tab="power">';
  echo '<h2><i class="fa fa-'.$osInfo["os_product"].'"></i> '.$osvInfo["osv_name"].'</h2>';
  echo '<div id="respondState" style="display:none;"></div>';
  echo '<div class="row">';
  echo '<div class="col-md-5" style="margin-bottom:10px;">';
  echo '<div id="areaStatus" class="ui segment inverted" style="height:100%;">';
  echo '<center><h2><i id="iconStatus" class=" icon"></i> <span id="txtStatus">Checking...</span></h2>Power State</center>';
  echo '</div>';

  echo '</div>';
  echo '<div class="col-md-7">';
  echo '<div class="form-vm-action ui form">';
  echo '<div style="margin-bottom:10px;"><a href="#power" id="vm_power" data-state="start" class="ui button massive labeled icon fluid"><i class="power icon"></i> <span id="powerTxt"> Loading</span></a></div>';
  echo '<div><a href="#restart" id="vm_restart" class="ui button massive orange labeled icon fluid"><i class="refresh icon"></i> Force Restart</a></div>';
  echo '</div>';
  echo '</div>';
  echo '</div>';
  echo '</div>';

  echo '<!--Other-->';
  echo '<div class="ui bottom attached tab segment" data-tab="other">';

  echo '<h2><i class="setting icon"></i> Other Configuration</h2>';

  echo '<div class="row">';

  echo '<div class="col-md-4">';
  echo '<center><button class="ui positive button fluid large open-modal" data-modal="confirm-box" data-action="reinstall_vm"><i class="download icon"></i> Re-Install</button></center>';
  echo '</div>';

  echo '<div class="col-md-4">';
  echo '<center><button class="ui button fluid orange large open-modal" data-modal="renew-box" data-action="renew_vm"><i class="calendar icon"></i> Renew VPS</button></center>';
  echo '</div>';

  echo '<div class="col-md-4">';
  echo '<center><button class="ui negative button fluid large open-modal" data-modal="confirm-box" data-action="remove_vm"><i class="remove user icon"></i> Destroy</button></center>';
  echo '</div>';

  echo '</div>';

  echo '<hr /><h2><i class="icon mail"></i> Ticket</h2>';
  $numAllReadMessage = $db->getNumber("SELECT * FROM ticket_conversation WHERE conversation_id=?",array( intval($_REQUEST["vm_id"]) ));
  $numAllUnReadMessage = $db->getNumber("SELECT * FROM ticket_conversation WHERE conversation_id=? AND has_read=?",array( intval($_REQUEST["vm_id"]) ,false));
  echo '<div class="alert alert-info" style="margin-top:10px;">';
  echo "<i class='icon info circle'></i> You have <b>{$numAllUnReadMessage}</b> unread message";
  echo '</div>';
  if($numAllReadMessage>=1){
    echo '<div class="list-group">';
    $msgInfo = $db->getRows("SELECT * FROM ticket_conversation WHERE conversation_id=? ORDER BY cid DESC",array( intval($_REQUEST["vm_id"]) ));
    $usageConversation = array();
    foreach($msgInfo as $row){
      $ticketInfo = $db->getRow("SELECT * FROM ticket WHERE conversation_id=?",array( intval($_REQUEST["vm_id"]) ));
      if(!in_array($row['conversation_id'],$usageConversation)){
        $usageConversation[] = $row['conversation_id'];
        if($row['has_read']==false){
          $isReadHTML = '<i class="icon mail"></i>';
        } else{ $isReadHTML = '<i class="icon check green"></i>'; }
        echo '<a href="./?page=dashboard&vm_id='.intval($_REQUEST['vm_id']).'&action=message&read='.$db->encryptText($row['conversation_id'],'conversation').'" class="list-group-item"> [<b style="color:purple;">TICKET</b>] '.htmlspecialchars($ticketInfo['title']).' <span class="pull-right">'.$isReadHTML.'</span></a>';
      }
    }
    echo '</div>';
  }

  echo '</div>';

  #============================================================
  aomFramework\viewer::load("modal.confirm.php");
  aomFramework\viewer::load("modal.renew.php");

  echo '</div>';
  echo "<script>$('.menu .item').tab();$('.selection.dropdown.renew-form').dropdown();var access_token = '".$_SESSION["user_info"]["access_token"]."';</script>";
} else{
  echo $er->warning("0x002","Not found this Virtual Machine!");
}
?>
