<div class="modal fade renew-box" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-refresh"></i> RENEW</h4>
      </div>
      <div class="modal-body">

        <?php
        date_default_timezone_set("Asia/Bangkok");
        $getMyVMInfo = $db->getRow("SELECT package_id,expire FROM vm WHERE vm_id=? AND uid=?",array( intval($_REQUEST['vm_id']) , intval($_SESSION["user_info"]["uid"]) ));
        $expTimestamp = $getMyVMInfo["expire"];
        $expTime = date( "Y-m-d H:i:s" , $expTimestamp );
        if( $expTimestamp >= time() ){
          $now = new DateTime('now');
          $future_date = new DateTime($expTime);
          $interval = $now->diff($future_date);
          $formatDate = "";
          if($interval->m > 0){ $formatDate.=" %m months"; }
          if($interval->d > 0){ $formatDate.=" %d days"; }
          if($interval->h > 0){ $formatDate.=" %h hours"; }
          $remaining = $interval->format($formatDate);

          echo '<div class="alert alert-info">';
          echo '<i class="icon info circle"></i>';
          echo 'Time remaining : <b>'.$remaining.'</b>';
          echo '</div>';
        } else{
          echo '<div class="alert alert-danger">';
          echo '<i class="icon warning circle"></i>';
          echo 'Your VM has Expired';
          echo '</div>';
        }
        ?>

        <div class="ui form">
          <div class="field">
            <label>Renew</label>
            <div class="ui selection dropdown renew-form">
              <input type="hidden" name="add_day">
              <i class="dropdown icon"></i>
              <div class="default text">Default 1 Month (Recommend)</div>
              <div class="menu">
              <?php
              $itemList = array();
              $priceList = array();
              $packageId = $getMyVMInfo['package_id'];
              $getPackageInfo = $db->getRow("SELECT vm_price FROM vm_package WHERE package_id=?",array($packageId));
              $pricePerMonth = $getPackageInfo['vm_price'];
              foreach($RenewMonthList as $d){
                $month = $d;
                $MonthOrYear = "Month";
                if($d%12==0){$month/=12;$MonthOrYear="Years";}
                $itemValue = $db->encryptText($d,'renew');
                $itemList[] = $itemValue;
                $priceList[] = doubleval(doubleval($d)*doubleval($pricePerMonth));
                echo '<div class="item" data-value="'.$itemValue.'">'.$month.' '.$MonthOrYear.'</div>';
              }
              echo '<script> var price = {';
              foreach($itemList as $n=>$i){
                echo "'{$i}':{$priceList[$n]}";
                if(count($itemList)-1 != $n ){echo ",";}
              }
              echo '}; </script>';
              ?>
              </div>
            </div>
          </div>
          <div class="field">
            <label>Total</label>
            <span><b style="font-size:18px;" id="total_price"><?=number_format($priceList[0],2)?></b> THB</span>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button class="btn btn-success" onclick="renewVM();"><i class="icon refresh"></i> Renew</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
