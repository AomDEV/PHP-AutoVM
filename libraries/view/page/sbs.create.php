<div class="ui three top attached steps">
  <div class="<?php if($_REQUEST["step"]==1){echo 'active';} else{echo 'disabled';} ?> step" data-step="1">
    <i class="windows icon"></i>
    <div class="content">
      <div class="title">OS</div>
      <div class="description">Choose OS</div>
    </div>
  </div>
  <div class="<?php if($_REQUEST["step"]==2){echo 'active';} else{echo 'disabled';} ?> step" data-step="2">
    <i class="server icon"></i>
    <div class="content">
      <div class="title">Plan</div>
      <div class="description">Choose plan</div>
    </div>
  </div>
  <div class="<?php if($_REQUEST["step"]==3){echo 'active';} else{echo 'disabled';} ?> step" data-step="3">
    <i class="check circle icon"></i>
    <div class="content">
      <div class="title">Verify</div>
      <div class="description">Verify order</div>
    </div>
  </div>
</div>
<div class="ui attached segment">
<form action="" method="post" name="next_step">
</form>
<!-- Store Data from last Step -->
<input type="hidden" id="_os" value="<?=intval($_REQUEST["os"])?>" />
<input type="hidden" id="_osv" value="<?=intval($_REQUEST["osv"])?>" />
<!-- Store data from last step -->
<?php
if(isset($_REQUEST["step"]) and is_numeric($_REQUEST["step"]) and $_REQUEST["step"]==1){
?>
<form action="<?=$_SERVER['REQUEST_URI']?>" method="get" name="create_vm">
  <!-- Select OS -->
  <h2><i class="icon file"></i> Select OS</h2>
<?php
include("libraries/view/template/select-os.php");
$osVersion = $db->getRows("SELECT osv_id,osv_name,os_id FROM vm_os_version;",array());
$versionList = array();
$osIDList = array();
foreach($osVersion as $ov){$osIDList[] = $ov['os_id'];$versionList['os-'.$ov["os_id"]]=array('name'=>$ov['osv_name'],'id'=>$ov['osv_id']);}
$jsValue = "{";
$firstVersion = 0;
for($i=1;$i<=count($versionList);$i++){
  $jsValue .= "'os-{$i}':{'name':'{$versionList['os-'.$i]['name']}','id':'{$versionList['os-'.$i]['id']}'}";
  if($i==count($versionList)){}else{$jsValue.=",";}
}
$jsValue.="}";
$jsVIO = "{";
for($i=1;$i<=count($versionList);$i++){
  $jsVIO .= "'os-{$i}':[";
    $vioSQL = $db->getRows("SELECT osv_id FROM vm_os_version WHERE os_id=?",array($i));
    $numVIO = 1;
    foreach($vioSQL as $vs){$numVIO++;$jsVIO.= '\''.$vs['osv_id'].'\'';if($numVIO==count($vioSQL)){$jsVIO.=",";}}
  $jsVIO.="]";
  if($i==count($versionList)){}else{$jsVIO.=",";}
}
$jsVIO .= "}";
print("<script>var versionList = {$jsValue};var version_in_os = {$jsVIO};</script>");
?>
<h2><i class="icon cube"></i> Select OS Version</h2>
<?php
include("libraries/view/template/select-osv.php");
?>
  <input type="hidden" name="osv" id="osv" value="<?=$firstVersion?>" />
  <!-- Select OS -->
</form>
<?php
} else if(isset($_REQUEST["step"]) and is_numeric($_REQUEST["step"]) and $_REQUEST["step"]==2 and isset($_REQUEST["os"]) and isset($_REQUEST["osv"]) and is_numeric($_REQUEST["os"]) and is_numeric($_REQUEST["osv"])){
?>
<!-- Select Plan -->
<form onsubmit="return false;" class="ui form create_vm_form segment basic compact">
<div>
<div class="row">

<?php
$getPackage = $db->getRows("SELECT package_id,package_name,vm_ram,vm_disk,is_ssd,vm_core,vm_price FROM vm_package",array());
$column=0;
$defaultPlan=0;
foreach($getPackage as $pno=>$package){
if($pno==0){$defaultPlan=$package["package_id"];}
$column++;
$vmRAM = $package["vm_ram"];
$vmDisk = $package["vm_disk"];
$pricePerMonth = $package["vm_price"];
$pricePerHour = $package["vm_price"]/30/24;
if(($vmRAM/1024)<1){$subfixRam="MB";echo $vmRAM;} else{$subfixRam="GB";$vmRAM/=1024;}
if(($vmDisk/1024)<1){$subfixStorage="MB";} else{$subfixStorage="GB";$vmDisk/=1024;}
if($package["is_ssd"]==false){$hddText="HDD";} else{$hddText="SSD";}
if($pno==0){$isActive="panel-primary";} else{$isActive="panel-default";}
?>

<div class="col-md-4" align="center">

<button type="button" class="thumbnail panel <?=$isActive?>" op="select_plan" data-plan="<?=$package["package_id"]?>" style="padding:0px">
  <div class="panel-heading" align="center">
    <h2><?=$package["package_name"]?></h2>
  </div>
  <div class="panel-body" align="left" style="color:#000;">
    <li><?=$package["vm_core"]?> CPU</li>
    <li><?=$vmRAM." ".$subfixRam?> RAM</li>
    <li><?=$vmDisk." ".$subfixStorage." ".$hddText?> Disk</li>
    <li>Support <b>24/7</b></li>
  </div>
  <div class="panel-footer" align="center" style="color:#000;">
    <b class="per-month-price"><?=number_format($pricePerMonth,2)?></b> <b><?=$SubfixFullCurrency?>/mo</b><br>
    <span class="text-muted" style="font-size:12px;"><?=number_format($pricePerHour,2)?> <?=$SubfixFullCurrency?>/hour</span>
  </div>
</button>

</div>

<?php
  if($column==3){echo '</div><div class="row">';$column=0;}
}
?>

</div> <!--Row-->
</div> <!--container-->
<input type="hidden" name="plan" id="plan" value="<?=$defaultPlan?>" />

<!-- Your Hostname -->
<?php
$random_hostname = $form->randomName(6,true).".".$_SERVER['SERVER_NAME'];
?>
<div class="field">
  <label><i class="tag icon"></i> Hostname</label>
  <div class="ui input huge">
    <input type="text" name="hostname" id="hostname" data-input="hostname" maxlength='30' required autocomplete="off" value="<?=$random_hostname?>" placeholder="Hostname" />
  </div>
</div>
<!-- Your Hostname -->
</form>
<!-- Select Plan -->
<?php
} else if(isset($_REQUEST["step"]) and is_numeric($_REQUEST["step"]) and $_REQUEST["step"]==3){
  $plan = intval($_REQUEST["plan"]);
  $osv = intval($_REQUEST["osv"]);
  $getPackage = $db->getRow("SELECT vm_price,package_name,vm_ram,vm_disk,is_ssd,vm_core FROM vm_package WHERE package_id=?",array( $plan ));
  $getOSV = $db->getRow("SELECT os_id,osv_name FROM vm_os_version WHERE osv_id=?",array( $osv ));
  $getOS = $db->getRow("SELECT os_product FROM vm_os WHERE os_id=?",array($getOSV["os_id"]));
  $account = $db->getRow("SELECT balance FROM account WHERE uid=?",array($_SESSION["user_info"]["uid"]));
  $packagePrice = $getPackage["vm_price"];
  $packageName = $getPackage["package_name"];
  $hostname = htmlspecialchars($_REQUEST["hostname"]);
  $packageMemory = $getPackage["vm_ram"];
  $packageDisk = $getPackage["vm_disk"];
  $packageCore = $getPackage["vm_core"];
  $osName = $getOSV["osv_name"];
  $osProduct = $getOS["os_product"];
  $myBalance = $account["balance"];
  $readableMemory = $packageMemory;
  $readableDisk = $packageDisk;
  $isReadyReadable = "<font style='color:green;'><i class='icon check'></i> Ready</font>";
  $subfixReadableMemory = "MB";
  $subfixReadableDisk = "MB";
  if($packageMemory/1024>=1){$readableMemory = $packageMemory/1024;$subfixReadableMemory="GB";}
  if(($packageDisk/1024)>=1){$subfixReadableDisk="GB";$readableDisk/=1024;}
  if($getPackage["is_ssd"]==false){$hddText="HDD";} else{$hddText="SSD";}
?>
<div style="margin-top:0px;">
  <h2><i class="icon check"></i> Verify your Order</h2>
  <div data-zone="alert" class="alert" style="display:none;"></div>
  <div class="ui segment" style="display:block;">
    <h3>Your Package : <b><i class="icon cloud"></i> <?=$packageName?></b> <span class="text-muted">(<?=number_format($packagePrice,2).$SubfixFullCurrency?>)</span></h3>
    <li>Hostname : <b><i class="icon server"></i> <?=$hostname?></b></li>
    <li>Memory : <b><i class="icon disk outline"></i> <?=$readableMemory." ".$subfixReadableMemory?></b></li>
    <li>Disk : <b><i class="icon disk outline"></i> <?=$readableDisk." ".$subfixReadableDisk." ".$hddText?></b></li>
    <li>OS : <b><i class="fa fa-<?=$osProduct?>"></i> <?=$osName?></b></li>
    <li>Core : <b><i class="icon cube"></i> <?=$packageCore?></b></li>
    <li>Balance : <b><i class="icon money"></i> <?=number_format($myBalance,2).$SubfixFullCurrency?></b></li>
    <li>Status : <b><?=$isReadyReadable?></b></li>
  </div>
</div>
<div style="display:none;" id="field_create_vm">
<?php
# Load version list
$osVersion = $db->getRows("SELECT osv_id,osv_name,os_id FROM vm_os_version;",array());
$versionList = array();
$osIDList = array();
foreach($osVersion as $ov){$osIDList[] = $ov['os_id'];$versionList['os-'.$ov["os_id"]]=array('name'=>$ov['osv_name'],'id'=>$ov['osv_id']);}
$jsValue = "{";
$firstVersion = 0;
for($i=1;$i<=count($versionList);$i++){
  $jsValue .= "'os-{$i}':{'name':'{$versionList['os-'.$i]['name']}','id':'{$versionList['os-'.$i]['id']}'}";
  if($i==count($versionList)){}else{$jsValue.=",";}
}
$jsValue.="}";
$jsVIO = "{";
for($i=1;$i<=count($versionList);$i++){
  $jsVIO .= "'os-{$i}':[";
    $vioSQL = $db->getRows("SELECT osv_id FROM vm_os_version WHERE os_id=?",array($i));
    $numVIO = 1;
    foreach($vioSQL as $vs){$numVIO++;$jsVIO.= '\''.$vs['osv_id'].'\'';if($numVIO==count($vioSQL)){$jsVIO.=",";}}
  $jsVIO.="]";
  if($i==count($versionList)){}else{$jsVIO.=",";}
}
$jsVIO .= "}";
print("<script>var versionList = {$jsValue};var version_in_os = {$jsVIO};var access_token = '".$_SESSION["user_info"]["access_token"]."';</script>");
?>
  <input type='hidden' id='os' value='<?=intval($_REQUEST["os"])?>' />
  <input type='hidden' id='osv' value='<?=intval($_REQUEST["osv"])?>' />
  <input type='hidden' id='plan' value='<?=intval($_REQUEST["plan"])?>' />
  <input type='text' id='hostname' value='<?=htmlentities($_REQUEST["hostname"])?>' />
</div>
<?php
} else {
  echo '<script>window.location="./?page=create&step=1";</script>';
}
?>
</div>
<div class="ui bottom attached segment secondary" align="right">
  <?php
  if(isset($_REQUEST["step"]) and is_numeric($_REQUEST["step"]) and $_REQUEST["step"]>1){
    $self = parse_url($_SERVER["REQUEST_URI"])["query"];
    $backURL = str_replace("step=".$_REQUEST["step"],"step=".($_REQUEST["step"]-1),$self);
    echo '<a href="#" onclick="sbs_submit(false);" class="back ui button negative big"><i class="left arrow icon"></i> Back</a>';
  }
  if($_REQUEST["step"]==3){
    echo '<button type="button" data-action="create_vm" onclick="this.disabled=true" class="ui button positive big"><i class="icon plus circle"></i> Verify</button>';
  } else{ echo '<a href="#" onclick="sbs_submit(true);" class="next sbs ui button positive big"><i class="check circle icon"></i> Next</a>'; }
  ?>
</div>
