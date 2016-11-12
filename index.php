<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
require_once("libraries/view/page/view.include.php");

$frontend->start_html();
$frontend->title($config["title"]);
$frontend->load("assets/css/bootstrap.min.css");
$frontend->load("assets/css/semantic.min.css");
$frontend->load("assets/css/tab.min.css");
$frontend->load("assets/css/font-awesome.min.css");
$frontend->load("assets/css/font-mfizz.css");
$frontend->load("assets/css/style.css");
$frontend->load("assets/js/jquery.min.js");
$frontend->load("assets/js/bootstrap.min.js");
$frontend->load("assets/js/semantic.min.js");
$frontend->load("assets/js/tab.min.js");
$frontend->load("assets/js/vm.js");
echo '<style>body{background-color:#000;}</style>';
$frontend->open_body();
if(isset($_SESSION["user_info"]) and $_SESSION["user_info"]["is_login"]==true){
	echo '<div align="center">';
	echo '<div style="padding-top:20px;padding-bottom:20px;max-width:900px;">';
	echo '<div class="ui container">';

	echo '<div class="rows">';
	 aomFramework\viewer::load("main.menu.php");
	echo '<div class="col-md-9">';
	echo '<div class="ui segment" align="left">';
	if(isset($_GET["page"]) and $_GET["page"]=="logout"){
		session_destroy();
		$form->js_redirect(3,"./#logout");
	} else if(isset($_REQUEST["page"]) and $_REQUEST["page"]=="dashboard"){
		echo '<h2 class="ui header"><i class="dashboard icon"></i> Virtual Machine</h2>';
		if(isset($_REQUEST["vm_id"]) and is_numeric($_REQUEST["vm_id"]) and $_REQUEST["vm_id"]>=1){
			if(isset($_REQUEST['action']) and file_exists(aomFramework\viewer::getViewerLocation()."dashboard.{$_REQUEST['action']}.php") ){
				aomFramework\viewer::load("dashboard.{$_REQUEST['action']}.php");
			} else{
				aomFramework\viewer::load("dashboard.manage.php");
			}
		} else{
			aomFramework\viewer::load("dashboard.index.php");
		}
	} else if(isset($_REQUEST["page"]) and $_REQUEST["page"]=="create"){
		if(file_exists(aomFramework\viewer::getViewerLocation().$_CREATE_SERVER_PAGE_TEMPLATE)){
			aomFramework\viewer::load($_CREATE_SERVER_PAGE_TEMPLATE);
		} else{ aomFramework\viewer::load("default.create.php"); }
	} else if(isset($_REQUEST["page"]) and $_REQUEST["page"]=="install" and isset($_REQUEST["vm"]) and is_numeric($_REQUEST["vm"])){
		aomFramework\viewer::load("create.install.php");
	} else if(isset($_REQUEST["page"]) and $_REQUEST["page"]=="transaction"){
		aomFramework\viewer::load("transaction.index.php");
	} else if(isset($_REQUEST["page"]) and $_REQUEST["page"]=="billing"){
		aomFramework\viewer::load("billing.index.php");
	} else if(isset($_REQUEST["page"]) and $_REQUEST["page"]=="laws"){ //Only hacker :D
		aomFramework\viewer::load("laws.index.php");
	} else if(isset($_GET["page"]) and isset($_SESSION["activeOrderResultPage"]) and $_SESSION["activeOrderResultPage"]==true and isset($_GET["application"]) and $_GET["page"]=="canceled"){
		aomFramework\viewer::load("billing.success.php");
	} else if(isset($_GET["page"]) and isset($_SESSION["activeOrderResultPage"]) and $_SESSION["activeOrderResultPage"]==true and isset($_GET["application"]) and $_GET["page"]=="succeed"){
		aomFramework\viewer::load("billing.failed.php");
	} else{
		$form->js_redirect(3,"./?page=dashboard");
		echo $er->fatal("0x002","Not found Cancel Page!");
	}
	echo '</div>';

	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';

} else{

	$form->open("login_form","post")
	->classCrop("field")->label("Username ")->classCrop("ui big icon input")->textbox("text","username","Username","autocomplete[off],required,maxlength[20]")->endCrop()->endCrop()
	->classCrop("field")->label("Password ")->classCrop("ui big icon input")->textbox("pass","password","Password","autocomplete[off],required,maxlength[50]")->endCrop()->endCrop()
	->button("button",'<i class="user plus icon"></i> Register','ui right floated teal compact labeled icon button',"?page=register")
	->button("submit",'<i class="user icon"></i> Login',"ui right floated green compact labeled icon button")
	->save();

	$form->open("register_form","post")
	->classCrop("field")->label("Username ")->classCrop("ui big icon input")->textbox("text","username","Username","autocomplete[off],required,maxlength[20]")->endCrop()->endCrop()
	->classCrop("field")->label("Password ")->classCrop("ui big icon input")->textbox("pass","password","Password","autocomplete[off],required,maxlength[50]")->endCrop()->endCrop()
	->classCrop("field")->label("Confirm Password ")->classCrop("ui big icon input")->textbox("pass","confirm_password","Confirm Password","autocomplete[off],required,maxlength[50]")->endCrop()->endCrop()
	->classCrop("field")->label("E-Mail ")->classCrop("ui big icon input")->textbox("email","email","E-Mail","autocomplete[off],required,maxlength[50]","email")->endCrop()->endCrop()
	->classCrop("field")->label("Phone ")->classCrop("ui big icon input")->textbox("text","phone","Phone","autocomplete[off],required,maxlength[10]","phone")->endCrop()->endCrop()
	->button("button",'<i class="user icon"></i> Login',"ui right floated teal compact labeled icon button","./")
	->button("submit",'<i class="user plus icon"></i> Register','ui right floated green compact labeled icon button')
	->save();

	if(isset($_GET) and isset($_GET["page"]) and $_GET["page"]=="register"){
		$page_name = "REGISTER";
	} else{$page_name = "LOGIN";}

	echo '<div align="center"><div class="ui segments" style="max-width:400px;padding:20px;">';
	echo '<div class="ui segment secondary clearing"><h2><i class="cloud icon"></i> '.$config["title"].' - <b>'.$page_name.'</b></h2></div>';
	echo '<div class="ui segment clearing"><div class="ui form" align="left">';

	if(isset($_GET["page"]) and $_GET["page"]=="register"){
		if($form->validate("register_form")->finish(3)==true){
			$user = $form->formValue("register_form","username");
			$pass = $form->formValue("register_form","password");
			$con_pass = $form->formValue("register_form","confirm_password");
			$email = $form->formValue("register_form","email");
			$phone = $form->formValue("register_form","phone");
			if($pass!=$con_pass){
				echo $er->return_warning("Password is not match!");
			} else if($db->login("account",array("username"=>$user,"email"=>$email),"OR")["status"]>=1){
				echo $er->return_warning("Username or E-Mail is already in use!");
			} else{
				$statusUser = 0;
				$succeed = false;
				if($SendMailToUserWhenRegistered==true){
					require 'libraries/mailer/PHPMailerAutoload.php';
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = ($EmailHost);
					$mail->Port = $EmailPort;
					$mail->SMTPSecure = $EmailSecure;
					$mail->SMTPAuth = $EmailAuth;
					$mail->Username = $EmailUsername;
					$mail->Password = $EmailPassword;
					$mail->Debugoutput = 'html';
					$mail->setFrom($EmailUsername, $EmailSendFromName);
					if(strlen($EmailReplyToEmail)>6){
						$ReplyName = $EmailReplyToEmail;
						if(strlen($EmailReplyToName)>=1){$ReplyName = $EmailReplyToName;}
						$mail->addReplyTo($EmailReplyToEmail, $ReplyName);
					}
					$mail->addAddress($email, $user);
					$mail->Subject = aomFramework\viewer::format($ETEXT_REGISTER_SUCCEED_SUBJECT,array("user"=>$user,"web_name"=>"9STEAM"));
					if($EnableEmailConfirm==false){
						$htmlRegisterEmail = "<h1>".aomFramework\viewer::format($ETEXT_HELLO_USERNAME,array("user"=>$user))."</h1><div>{$ETEXT_THANK_REGISTERED_WITHOUT_CONFIRM}</div><div><p>Username : <b>{$username}</b></p><p>Password : <b>{$password}</b></p></div><div>".aomFramework\viewer::format($ETEXT_BEST_REGARD,array("webname"=>$config["title"]))."</div>";
					} else{
						$statusUser = 1;
						$tokenKey = md5(time().$pass.$user);
						$confirmationKey = $db->encryptText(json_encode(array("username"=>$user,"email"=>$email)),$tokenKey);
						$generateLinkConfirm = str_replace("//","/",$config["urlPath"]."/?confirmation={$confirmationKey}&token={$tokenKey}");
						$htmlRegisterEmail = "<h1>".aomFramework\viewer::format($ETEXT_HELLO_USERNAME,array("user"=>$user))."</h1><div>{$ETEXT_THANK_REGISTERED_WITH_CONFIRM}</div><div><p>Verify account <b><a href='{$generateLinkConfirm}'>{$generateLinkConfirm}</a></b></p></div><div>".aomFramework\viewer::format($ETEXT_BEST_REGARD,array("webname"=>$config["title"]))."</div>";
					}
					$mail->msgHTML($htmlRegisterEmail);
					$mail->AltBody = "Thank you for registering with us.";
					if (!$mail->send()) {
					    echo "Mailer Error: " . $mail->ErrorInfo;
					} else {
							$succeed = true;
					    echo "Message sent!";
					}
				} else{
					$succeed = true;
					echo $er->return_success("Register successful!");
					echo $er->redirect(5);
				}

				if($succeed == true){
					$db->insert("account",array("username"=>strtolower($user),"password"=>$db->passwordEncrypt($pass),"balance"=>$StartAtRegisterMoney,"email"=>strtolower($email),"phone"=>"66".substr($phone,1),"last_login"=>"0","last_token"=>"0","status"=>$statusUser));
				}
			}
		}

		echo ($form->render("register_form"));
	} else{
		if($form->validate("login_form")->finish(3)==true){
			$user = $form->formValue("login_form","username");
			$pass = $db->passwordEncrypt($form->formValue("login_form","password"));
			$checkLogin = $db->login("account",array("username"=>$user,"password"=>($pass)));
			if(isset($checkLogin["status"]) and $checkLogin["status"]==true){
				if($checkLogin["info"][0]["status"]==0){
					$access_token=$db->generate_access_token($checkLogin["info"][0]["uid"]);
					@$db->update("account",array("last_token"=>$access_token,"last_login"=>time()),"uid","=",$checkLogin["info"][0]["uid"]);
					@$_SESSION["user_info"]=array("is_login"=>true,"access_token"=>$access_token,"uid"=>$checkLogin["info"][0]["uid"]);
					echo $er->return_success("Login successful!");
					$form->js_redirect(3,"./?page=dashboard#token={$access_token}");
				} else if($checkLogin["info"][0]["status"]==1){
					echo $er->return_warning("Please confirm your account first!");
				} else if($checkLogin["info"][0]["status"]==9){
					echo $er->return_warning("Your account has been suspended!");
				} else{
					echo $er->return_warning("Account information incorrect!");
				}
			} else{
				echo $er->return_warning("Username or Password is incorrect!");
			}
		}

		echo ($form->render("login_form"));
	}
	echo '</div>';
	echo '</div></div></div>';
}
$frontend->end_html();
?>
