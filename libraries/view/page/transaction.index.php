<h2 class="ui header"><i class="shopping cart icon"></i> Transaction</h2>

<div class="table-responsive" style="margin-left:-15px;margin-right:-15px;">
  <table class="table table-hover">
    <tr class="active">
      <th><center>#</center></th>
      <th><center><i class="shopping cart icon"></i> Transacion ID</center></th>
      <th><center><i class="setting icon"></i> Payment Type</center></th>
      <th><center><i class="money icon"></i> Amount</center></th>
      <th><center><i class="calendar icon"></i> Time</center></th>
    </tr>
    <?php
    $billing = $db->getRows("SELECT * FROM a_billing_report WHERE user_id=?",array($form->get_session($_SESSION,"uid")));
    foreach($billing as $no=>$log){
      $no++;
      $account_type = ucwords(str_replace("."," ",$log['account_type']));
      $amount = number_format($log["amount"],2);
      if($log["status"]==0){$class="info";} else if($log["status"]==1){$class="success";} else{$class="danger";}
      echo "<tr class='{$class}'>";
      echo "<td><center>{$no}</center></td>";
      echo "<td><center style='font-size:12px;'>{$form->shortTXID($log['transaction_id'])}</center></td>";
      echo "<td><center>{$account_type}</center></td>";
      echo "<td><center>{$amount}{$SubfixCurrency}</center></td>";
      echo "<td><center>{$form->timestampToThaiDate($log['transaction_time'])}</center></td>";
      echo "</tr>";
    }
    ?>
  </table>
</div>

<div align="right">
<h4>
  Total : <?php echo number_format($db->getRow("SELECT SUM(amount) FROM a_billing_report WHERE user_id=?",array($form->get_session($_SESSION,"uid")))["SUM(amount)"],2).$SubfixCurrency; ?>
</h4>
</div>
