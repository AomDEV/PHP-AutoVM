<?php
require("applications/class.xen.php");
use Xen as Xen;
$ip = "192.168.159.129";
$user = "root";
$pass = "Lomer123";
$xen = new Xen\xen($ip, $user, $pass);

if(isset($_GET["label"])){

} else{
	echo '<h2>All VMs</h2>';
	echo '<ul>';
	$allVM = $xen->VM_get_all();
	foreach ($allVM as $vm) {
	    $record = $xen->VM_get_record($vm);
	    if($record["is_a_template"]==false and $record["is_control_domain"]!=1){
	    	echo "<li><a href='?label={$record['name_label']}'>".$record["name_label"].'</a></li>';
	    }
	}
	echo '<ul>';
}
?>