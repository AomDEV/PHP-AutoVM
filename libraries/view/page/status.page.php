<?php
if(isset($_GET) and isset($_GET["status"]) and $_GET["status"]=="success"){
  echo '<span style="font-size:72px;"><center><img width="150" src="assets/img/success.png" /></center></span>';
  echo '<center><h2>SUCCESSFUL!</h2></center>';
} else{
  echo '<span style="font-size:72px;"><center><img width="150" src="assets/img/error.png" /></center></span>';
  echo '<center><h2>FAILURE!</h2></center>';
}
?>
