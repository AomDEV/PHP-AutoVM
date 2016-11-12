<?php if($this->type_code==0){$bgcl="#FF8B8B";$bocl="red";$tcol="#730000";}else{$bgcl="#FFC900";$bocl="#FF9E00";$tcol="#732F00";} ?>
<div style="background-color:<?php echo "{$bgcl}"; ?>;padding:10px;border:2px solid <?php echo "{$bocl}"; ?>;">
<div style="font-size:24px;"><?php echo "<b style='color:{$tcol};'>{$this->type}</b> : {$this->title} ({$this->error_code})"; ?></div>
<div style=""><?php echo "{$this->detail}"; ?></div>
</div>