<?php
$application = array("paypal"=>"PayPal","cc"=>"Credit Card");
if(isset($application[$_GET["application"]])){
  $title = $application[$_GET["application"]];
  echo "<h2><i class='remove circle icon'></i>Cancel Order</h2><hr style='margin-top:0px;margin-bottom:10px;' />";
  if(isset($_GET["token"]) and preg_match("/^[a-zA-Z0-9\-]*$/",$_GET["token"])==TRUE){$token="(".htmlspecialchars($_GET["token"]).")";} else{$token=null;}
  echo "".$er->return_warning("Your process has been canceled! <b>{$token}</b>")."";
  echo '<hr style="margin-top:10px;margin-bottom:5px;" /><a href="./" class="ui button negative labeled icon"><i class="left arrow icon"></i> Back</a>';
  $_SESSION["activeOrderResultPage"]=false;
  unset($_SESSION["activeOrderResultPage"]);
} else{echo $er->fatal("0x002","Not found Cancel Page!");}
?>
