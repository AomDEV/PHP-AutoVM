<?php
$uid = $_SESSION['user_info']['access_token'];
$numCheck = $db->getNumber("SELECT * FROM vm WHERE vm_id=? AND ip_updated=? AND uid=?",array(intval($_REQUEST["vm"]),false,intval($uid)));
if($numCheck<=1){
?>
<h1><i class="fa fa-cog fa-spin"></i> Setting up VM...</h1>
<div class="status_checking alert" style="display:none;"></div>
<div class="progress">
  <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
    <span class="sr-only">Setting up VM...</span>
  </div>
</div>

<div class="ui segment">
  <h2><i class="server icon"></i> VM Info</h2><hr />
  <div style="font-size:18px;">
  <?php
  $getVM = $db->getRow("SELECT * FROM vm WHERE vm_id=?",array(intval($_REQUEST["vm"])));
  $package = $db->getRow("SELECT package_name FROM vm_package WHERE package_id=?",array( intval($getVM['osv_id']) ) );
  echo "<div><i class='icon cube'></i> Hostname : <b>{$getVM['hostname']}</b></div>";
  echo "<div style='margin-top:10px;'><i class='icon disk outline'></i> Memory : <b>{$getVM['memory']}</b></div>";
  echo "<div style='margin-top:10px;'><i class='icon disk outline'></i>Disk : <b>{$getVM['disk']}</b></div>";
  echo "<div style='margin-top:10px;'><i class='icon tag'></i> Package Name : <b>{$package['package_name']}</b></div>";
  ?>
</div>
</div>
<script>var access_token = "<?=$uid;?>";window.onbeforeunload = confirmExit;setInterval(function(){checkingSetupVM()},5000);</script>
<?php
} else{
  echo '<h1><i class="fa fa-cog"></i> Setup VM</h1>';
  echo '<div class="alert alert-danger"><i class="icon warning circle"></i> Not found VM</div>';
  exit();
}
?>
