<?php
$firstVersion=$versionList['os-'.$defaultOS]['id'];
for($i=0;$i<count($osList);$i++){
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
