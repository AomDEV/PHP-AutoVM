<?php
## Load Function and Use Namespace ##
require_once('libraries/http_client/vendor/autoload.php');
include_once('applications/AES.php');
require_once("applications/class.xen.php");
use Xen\xen;
## Load Function and Use Namespace ##
$checkVM = $db->login("ticket",array("conversation_id"=> @$db->decryptText( $_REQUEST['read'],'conversation') ));
if($checkVM['status']==true){
  $uid = $form->get_session($_SESSION,"uid");
  $myInfo = $db->getRow("SELECT username FROM account WHERE uid=?", array($uid) );
  $sqlAllMessage = "SELECT * FROM ticket_conversation WHERE conversation_id=?";
  $sqlTicketInfo = "SELECT * FROM ticket WHERE conversation_id=?";
  $readID = $db->decryptText( $_REQUEST['read'] ,"conversation");
  $getFirstInfo = $db->getRow($sqlAllMessage,array($readID));
  $getTicketInfo = $db->getRow($sqlTicketInfo,array($readID));
  echo "<script>var myUsername = '{$myInfo['username']}';var access_token = '{$_SESSION['user_info']['access_token']}';</script>";
?>
<hr /><h3><i class="icon mail"></i> Read Message <spa class="text-muted">(<?=$getTicketInfo['title']?>)</span></h3>
<div class="ui segment" id="chat_zone">

<?php
$getMessage = $db->getRows($sqlAllMessage,array($readID));

echo '<div align="right">';
echo '<div class="chat-me" align="left"><h3>[<b style="color:blue;">'.$myInfo['username'].'</b>] <span class="text-muted" style="font-size:12px;">Open at : <b>'.date('m/d/Y H:i:s', $getTicketInfo['time']).'</b></span></h3>'.$getTicketInfo['message'].'</div>';
echo '</div>';

foreach($getMessage as $msg){
  if($msg['uid']==0){$getSenderName = "<b style='color:red;'>STAFF</b>";} else{$getSenderName = "<b style='color:blue;'>".$db->getRow("SELECT username FROM account WHERE uid=?",array($msg['uid']))['username']."</b>";}
  $oneDay = strtotime(date('d-m-Y H:i:s',strtotime(date("d/m/Y H:i:s") . "+1 days")));
  if($oneDay < time()){ $time = date('H:i:s A' , $msg['time']); } else{ $time = date('d-m-Y H:i:s', $msg['time']); }
  if($msg['uid']!=$uid){ $classChat = "chat-other";$alignChat = "left"; } else{ $classChat = "chat-me";$alignChat = "right"; }
  if($msg['uid']!=$uid){ @$db->query("UPDATE ticket_conversation SET has_read=? WHERE cid=?;",array( true,$msg['cid'] )); }
  echo '<div align="'.$alignChat.'">';
  echo '<div class="'.$classChat.'" align="left"><h3>['.$getSenderName.'] <span class="text-muted" style="font-size:12px;">'.$time.'</span></h3>'.$msg['message'].'</div>';
  if($msg['uid']==$uid and $msg['has_read']==true){
    echo '<div class="text-muted read-chat"><i class="icon check"></i> Read</div>';
  }
  echo '</div>';
}
?>
</div>
<div class="ui action input fluid huge">
  <input type="text" class="message-box" placeholder="Type your message here...">
  <button class="ui button positive icon labeled send-message"><i class="icon send"></i> Send</button>
</div>
<?php
} else{
?>
<hr /><div class="alert alert-danger"><i class="icon warning circle"></i> Not found message!</div><a href="./?page=dashboard&vm_id=<?=intval($_REQUEST['vm_id'])?>" class="btn btn-info"><i class="icon home"></i> Back</a>
<?php
}
?>
