<?php # Allow only include from view folder ?>
<div class="field" align="center">
<div align="center" class="ui grid stackable">
  <?php
  $osList = array();
  $os = $db->getRows("SELECT os_product,os_name,os_code,os_id FROM vm_os;",array());
  $i=0;
  $defaultOS = "";
  foreach($os as $io=>$o){ $os_name=$o["os_name"];$os_icon = ucfirst("{$o['os_product']}.png");
  if($io==0){$defaultOS = $o["os_id"];}
  $i++;
  $osList[]=$o["os_product"];
  if($defaultOS==$o["os_id"]){$isActive="teal";} else{$isActive="grey";}
  ?>
  <div class="four wide column">
  <button type="button" op="select_os" data-os="<?=$o["os_id"]?>" class="thumbnail <?=$isActive?> ui fluid button btn" title="<?=$os_name?>">
    <center>
      <img src="./assets/img/os/<?=$os_icon?>" width="96" />
      <div><?=ucfirst($o['os_product'])?></div>
    </center>
  </button>
  </div>
  <?php
    if($i==4){echo '</div><div class="four wide column">';$i=0;}
  }
  ?>
</div>
</div>
<input type="hidden" name="os" id="os" value="<?=$defaultOS?>" />
