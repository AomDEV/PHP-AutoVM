<?php
$frontend->load("assets/css/font-awesome.min.css");
$frontend->load("assets/css/dropdown.css");
$frontend->load("assets/css/bootstrap-datetimepicker.css");
$frontend->load("assets/js/moment-with-locales.min.js");
$frontend->load("assets/js/bootstrap-datetimepicker.min.js");
$frontend->load("assets/js/jquery.maskMoney.min.js");
$frontend->load("assets/js/dropdown.js");
$frontend->load("assets/js/jquery.creditCardValidator.js");
$frontend->load("assets/js/billing-payment.js");
//include('applications/AES.php');

$user = $db->select("account")->find("uid","=",$form->get_session($_SESSION,"uid"))->execute()[0];
echo '<div class="ui header"><h2><i class="bookmark icon"></i> Billing <span class="pull-right text-muted" style="font-style:small-caps;">Balance <b>'.number_format($user["balance"],2).'</b> THB</span></h2></div><hr />';

echo '<div id="credit_card" style="margin-bottom:20px;">';
echo '<h3><i class="payment icon"></i> Credit Card</h3>';
$generateCardSearch = $db->select("credit_card")->find("user_id","=",$form->get_session($_SESSION,"uid"))->execute(true);
$checkCard = $db->getNumber($generateCardSearch,array());
if($checkCard==1){
  $infoCard = $db->getRow($generateCardSearch,array());
  $dFirstName = $infoCard["first_name"];
  $dLastName = $infoCard["last_name"];
  $decryptCardNo = $db->decryptText($infoCard["card_no"],"CVV2|{$infoCard["card_cvv2"]}");
  $dCardNo = substr($decryptCardNo, strlen($decryptCardNo)-4, 4);
  $textCard="{$dFirstName} {$dLastName} - XXXX {$dCardNo}";
} else{ $textCard = "Not Found <b>Credit Card</b>"; }
echo '<div id="load_segment" class="ui segment inverted grey">';
echo '<div id="showCard" style="display:block;">';
echo '<div class="addFundxAlert alert alert-warning" style="display:none;"></div>';
echo '<font size=5>'.$textCard.'</font>';

echo '<div class="pull-right">';
echo '<a href="#" data-action="addFund" class="ui button circular icon mini"><i class="icons"><i class="credit card icon"></i><i class="corner add icon"></i></i></a>';
echo '<a href="#" data-action="editCard" class="ui button circular icon mini"><i class="pencil icon"></i></a>';
echo '</div>';

#=========================================================
echo '<div class="addFundBox" style="display:none;">';

echo '<div align="left">';
echo '<select class="ui selection dropdown" id="amount" name="amount">';
for($i=0;$i<count($creditCardAmount);$i++){
  echo '<option value="'.$db->encryptText($i,"amount_key").'">฿'.number_format($creditCardAmount[$i],2).' THB</option>';
}
echo '</select> ';
echo '<span><button class="ui teal left labeled icon button" data-action="addFund" style="margin-top:5px;"><i class="add icon"></i> Add Fund</button></span>';
echo '</div>';

echo '</div>';
#=========================================================

echo '</div>';
echo '<div id="editZone" style="display:none;">';

$cardType = array("VISA","MasterCard","Discover","Amex","JCB");
$countries = json_decode(file_get_contents("libraries/countries.json"),true);
ksort($countries);
$form->open("edit_card","","","ui inverted form")
->classCrop("alert alert-warning xAlert")->i("warning circle icon")->string(" To change the information, System will deductions your balance less than 1 baht to check this card is correct. (No refund)")->endCrop()
->h3("ui dividing inverted header")->string("YOUR DETAILS")->endh3()
->classCrop("two fields")
->classCrop("field first_name")->label("First Name")->classCrop("ui input")->textbox("text","first_name","First Name","id[first_name],autocomplete[off],required,maxlength[30]","name")->endCrop()->endCrop()
->classCrop("field last_name")->label("Last Name")->classCrop("ui input")->textbox("text","last_name","Last Name","id[last_name],autocomplete[off],required,maxlength[40]","name")->endCrop()->endCrop()
->endCrop()
->classCrop("two fields")
->classCrop("field phone")->label("Phone Number")->classCrop("ui input")->textbox("text","phone","Phone Number","id[phone],autocomplete[off],required,maxlength[10]","phone")->endCrop()->endCrop()
->classCrop("field address")->label("Street Address")->classCrop("ui input")->textbox("text","address","Street Address","id[address],autocomplete[off],required,maxlength[220]","address")->endCrop()->endCrop()
->endCrop()
->classCrop("two fields")
->classCrop("field city")->label("City")->classCrop("ui input")->textbox("text","city","City","id[city],autocomplete[off],required,maxlength[100]","name")->endCrop()->endCrop()
->classCrop("field state")->label("State")->classCrop("ui input")->textbox("text","state","State","id[state],autocomplete[off],required,maxlength[16]","name")->endCrop()->endCrop()
->endCrop()
->classCrop("two fields")
->classCrop("field postalcode")->label("Postal Code")->classCrop("ui input")->textbox("text","postalcode","Postal Code","id[postalcode],autocomplete[off],required,maxlength[5]","name")->endCrop()->endCrop()
->classCrop("field country")->label("Country")->select(($countries),"country","class[ui search dropdown fluid]","name")->endCrop()
->endCrop()
->h3("ui dividing inverted header")->string("CREDIT CARD DETAILS")->endh3()
->classCrop("field card_no","status_no")->label("<b>Credit Card Number</b>")->classCrop("ui left icon input")
->i("visa icon","cardIcon")
->textbox("text","card_no","Credit Card Number","id[card_no],autocomplete[off],required,maxlength[16]","int")->endCrop()->endCrop()
->hidden("","card_type","card_type")
->classCrop("two fields")
->classCrop("field card_cvv2")->label("CVV2")->classCrop("ui input")->textbox("pass","card_cvv2","CVV2","id[card_cvv2],autocomplete[off],required,maxlength[4]","int")->endCrop()->endCrop()
->classCrop("field card_exp","status_exp")->label("Card Expires MM/YYYY")->classCrop("ui input")->textbox("text","card_exp","Card Expires MM/YYYY","id[card_exp],autocomplete[off],required,maxlength[7]","card_exp")->endCrop()->endCrop()
->endCrop()
->right()
->hidden("check_card","initType","initType")
->button("button","<i class='check circle icon'></i> Save","ui positive labeled icon button","",true,"data-action='payment'")
->button("button","Close","ui negative button","",false,"data-action='hideCardForm'")
->endRight()
->save();
echo $form->render("edit_card");
echo '</div>';
echo '<div align="center" id="status_page"></div>';
echo '</div> <!--.ui segment inverted grey-->';
echo '</div> <!--#credit_card-->';

echo '<div id="paypal" class="row" style="margin-bottom:20px;">';
echo '<div class="col-md-7">';
echo '<h3><i class="paypal icon"></i> PayPal</h3>';
echo '<div class="ui segment inverted grey">';
echo '<span><select name="paypal_amt" class="ui selection fluid inverted dropdown" id="paypal_amt">';
for($i=0;$i<count($paypalAmount);$i++){
  echo "<option value='".$db->encryptText($i,"!paypal_amount")."'>Pay ".number_format($paypalAmount[$i],2)." THB</option>";
}
echo '</select></span> <div style="margin-top:10px;"><button data-action="payment" id="paypal" class="ui fluid large button positive labeled icon"><i class="paypal icon"></i> Pay Now</button></div>';
echo '<form id="paypalAction" method="post"><input type="hidden" name="price" id="price" value="'.time().'" /></form>';
echo '<script>$("select.dropdown").dropdown();</script>';
echo '</div>';
echo '</div>';
echo '<div class="col-md-5">';
echo '<div style="margin-top:10px;" class="ui segment secondary"><div style="font-size:16px;margin-bottom:12px;"><i class="clock icon"></i> <b>Information</b></div>Your transaction will be process in <b>5 minutes</b>. If you not recieve balance in 5 minutes (auto), Please <b>contract support team</b></div>';
echo '</div>';
echo '</div>';

echo '<div id="bank">';
echo '<h3><i class="bookmark icon"></i> Bank Transfer</h3>';
echo '<div class="row">';
echo '<div class="col-md-5">';
echo '<div class="table-responsive">';
echo '<table class="table table-hover">';
$getBillingDetail = $db->select("a_billing_account")->find("username","=","bank")->execute(true);
$sqlRows = $db->getRows($getBillingDetail);
$makeSelectArray = array();

foreach($sqlRows as $link=>$row){
  $id=$db->encryptText($row["id"],"bank_id");
  $makeSelectArray[$id]=strtoupper($row["account_type"])." - ".$row["password"];
  if($row["username"]=="bank"){
    echo '<tr>';
    echo '<th style="vertical-align: middle"><center><img width="28" title="'.strtoupper($row["account_type"]).'" src="./assets/img/payment/'.$row["account_type"].'.png" /></center></th>';
    echo '<td><p><b>'.$row["password"].'</b></p><p style="margin-top:-15px;">'.$row["account_name"].'</p></td>';
    echo '</tr>';
  }
}
echo '</table>';
echo '</div>';
echo '</div>';
echo '<div class="col-md-7">';
$form->open("reporter_form","post","","ui form")
->classCrop("field")->label("Bank Account")->select($makeSelectArray,"bank_account","class[ui dropdown fluid]","encrypt_text")->endCrop()
->classCrop("field")->label("Transaction time")->classCrop("ui input")->textbox("text","tx_time","Transaction Time","value[".date("m-d-Y H:i")."],id[tx_time],autocomplete[off],required,value[".date("d/m/Y H:i")."]","date_time")->endCrop()->endCrop()
->classCrop("field")->label("Amount")->classCrop("ui input")->textbox("text","amount","Amount","value[100.00],data-prefix[฿],id[amount],autocomplete[off],required,maxlength[12]","currency")->endCrop()->endCrop()
->classCrop("")->button("submit","<i class='check circle icon'></i> Confirm","ui positive labeled icon button","",true)->endCrop()
->save();

if($form->validate("reporter_form")->finish(3)==true){
  $bankAccount = $db->decryptText($form->formValue("reporter_form","bank_account"),"bank_id");

  $txTime = ($form->formValue("reporter_form","tx_time"));
  $dtime = DateTime::createFromFormat("m-d-Y H:i", $txTime);
  $txStamp = $dtime->getTimestamp();

  $amount = $form->convertCurrencyToFloat($form->formValue("reporter_form","amount"));
  $genSQLBankId = $db->select("a_billing_account")->find("id","=",$bankAccount)->execute(true);
  $checkBankId = $db->getNumber($genSQLBankId,array());
  if($txStamp < strtotime('-1 month') ){
    echo $er->return_warning("This transaction more than 1 month");
  } else if(!is_numeric($bankAccount) or $checkBankId<=0){
    echo $er->return_warning("Not found Bank ID");
  } else if($amount<100){
    echo $er->return_warning("Minimum amount is 100THB! ($amount)");
  } else{
    @$db->insert("a_billing_report",array("id"=>NULL,"account_type"=>"bank","user_id"=>$form->get_session($_SESSION,"uid"),"account_id"=>$bankAccount,"transaction_time"=>$txStamp,"amount"=>$amount,"transaction_id"=>$form->generateTXID(),"status"=>0,"last_report"=>time()));
    echo $er->return_success("Transaction Sent");
  }
}

echo $form->render("reporter_form");
$form->executeJS("\$(function(){\$('#tx_time').datetimepicker({format:'MM-DD-YYYY HH:mm',icons:{time:'fa fa-clock-o',date:'fa fa-calendar',up:'fa fa-chevron-up',down:'fa fa-chevron-down',previous:'fa fa-chevron-left',next:'fa fa-chevron-right',today:'fa fa-crosshairs',clear:'fa fa-trash'}});});");
$form->executeJS("\$('#amount').maskMoney();");

echo '</div>';
echo '</div>';
echo '</div>';
?>
