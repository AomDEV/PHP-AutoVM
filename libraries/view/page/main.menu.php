<div class="col-md-3">
<div class="list-group" align="left">
<li class="list-group-item active" style="background-color:#777;color:#fff;text-align:center;"><b><h1><i class="cloud icon"></i> <?=$config['title']?></h1></b></li>
<a href="?page=create" class="list-group-item list-group-item-success positive active"><i class="server icon"></i> Create VM</a>
<?php if(isset($_GET["page"]) and $_GET["page"]=="dashboard"){ $activeText = "active";} else{$activeText = null;} ?>
<a href="?page=dashboard" class="list-group-item <?=$activeText?>"><i class="dashboard icon"></i> Virtual Machine</a>
<?php if(isset($_GET["page"]) and $_GET["page"]=="transaction"){ $activeText = "active";} else{$activeText = null;} ?>
<a href="?page=transaction" class="list-group-item <?=$activeText?>"><i class="shopping cart icon"></i> Transaction</a>
<?php if(isset($_GET["page"]) and $_GET["page"]=="billing"){ $activeText = "active";} else{$activeText = null;} ?>
<a href="?page=billing" class="list-group-item <?=$activeText?>"><i class="bookmark icon"></i> Billing</a>

<a href="?page=logout" onclick="return confirm('Are you sure?');" class="list-group-item list-group-item-danger active"><i class="sign out icon"></i> Sign Out</a>

</div>
</div>
