<div class="table-responsive" style="margin-left:-15px;margin-right:-15px;">
<table class="table table-hover">
<tr class="active"><th><center>#</center></th> <th><center>OS</center></th> <th><center>Host Name</center></th> <th><center>Region</center></th> <th><center>Status</center></th> <th><center>Action</center></th></tr>
<?php
$checkNum = $db->login("vm",array("uid"=>$form->get_session($_SESSION,"uid")));
if($checkNum["status"]==true){
  foreach($checkNum["info"] as $row){
    $getOSV = $db->select("vm_os_version")->find("os_id","=",$row["osv_id"])->execute()[0];
    $getOS = $db->select("vm_os")->find("os_id","=",$getOSV["os_id"])->execute()[0];
    $getRegion = $db->select("vm_region")->find("region_id","=",$row["region"])->execute()[0];
    if($row["status"]==0){
      $status="<font style='color:green;'>Active</font>";
    } else{
      $status="<font style='color:red;'>Suspend</font>";
    }

    echo '<tr>';
    echo '<td><center>'.$row['vm_id'].'</center></td>';
    echo '<td><center><i title="'.$getOS['os_name'].'" class="fa fa-'.$getOS['os_product'].'"></i></center></td>';
    echo '<td><center>'.$row['hostname'].'</center></td>';
    $flagLowerID = strtolower($getRegion['region_code']);
    echo '<td><center><i title="'.$getRegion['region_name'].'" class="'.$flagLowerID.' flag"></i></center></td>';
    echo '<td><center><b>'.$status.'</b></center></td>';
    echo '<td><center><div class="ui icon buttons mini"><a class="ui primary button" href="?page=dashboard&vm_id='.$row['vm_id'].'"><i class="setting icon"></i></a> <a class="ui button negative" href="./?page=dashboard&vm_id='.$row['vm_id'].'&act=restart"><i class="refresh icon"></i></a></div></center></td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="6"><center>Not found VM!</center></td></tr>';
}
?>
</table>
</div>
