<h2 class="ui header"><i class="server icon"></i> Create Server</h2>

<?php echo '<script>var access_token="'.$_SESSION["user_info"]["access_token"].'";</script>'; ?>

<div data-zone="alert" class="alert" style="display:none;"></div>

<form class="ui form create_vm_form segment basic compact" onsubmit="javascript:return false;">

<!-- Select OS -->
<div class="ui segment blue compact">
  <?php include("libraries/view/template/select-os.php"); ?>
</div>
<!-- Select OS -->

<!-- Select Version -->
<div class="">
  <div align="center">

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
print("<script>var versionList = {$jsValue};var version_in_os = {$jsVIO};</script>");
$firstVersion=$versionList['os-'.$defaultOS]['id'];
for($i=1;$i<count($osList);$i++){
  if(isset($osIDList[$i])){
    $thisOSId = $osIDList[$i];
    if($thisOSId==$defaultOS){$styleShow = "display:block;";} else{$styleShow = "display:none;";}
    ?>
    <div class="field" style="<?=$styleShow?>" data-zone="select_osv" id="select_version_os_<?=$thisOSId?>">
      <div class="ui grid three column stackable">
          <?php
          $defaultVersion = $db->getRows("SELECT osv_id,osv_name FROM vm_os_version WHERE os_id=?",array($thisOSId));
          foreach($defaultVersion as $vi=>$do){
            if($do['osv_id']==$firstVersion){$isActive="teal";} else{$isActive="grey";}
            echo '<div class="column">';
            echo '<button op="select_osv" data-osv="'.$do['osv_id'].'" type="button" class="thumbnail '.$isActive.' ui fluid button btn">';
            echo '<i class="icon setting"></i>';
            echo "<div>{$do['osv_name']}</div>";
            echo '</button>';
            echo '</div>';
            if($vi==3){echo '</div><div class="ui grid three column stackable">';}
          }
          ?>
      </div>
    </div>
    <?php
  } else{
    // Not found version of this OS
  }
}
?>
  <input type="hidden" name="osv" id="osv" value="<?=$firstVersion?>" />
</div>
</div><hr />
<!-- Select Version -->

<!-- Select Plan -->
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
<input type="hidden" name="plan" id="plan" value="<?=$defaultPlan?>" />
<!-- Select Plan -->

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

<hr />
<div align="right">
<button type="button" class="ui button positive huge" data-action="create_vm"><i class="icon plus"></i> Create VM</button>
</div>
</form>
