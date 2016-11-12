<h1 align="center" class="alert alert-danger"><i class="icon warning circle"></i> FAILURE<div style="font-size:16px;"><?php if(!isset($_REQUEST['reason'])){ echo "Your transaction is failed!"; } else{ echo "Reason : ".htmlspecialchars(urldecode($_REQUEST['reason'])); } ?></div></h1>
<a href="#" class="btn btn-info" onclick="window.location='./'"><i class="icon home"></i> Home</a>
