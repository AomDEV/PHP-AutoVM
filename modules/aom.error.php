<?php
namespace aomFramework;
class error{
	public $type="Fatal:";
	public $title="";
	public $error_code="";
	public $detail="";
	public $type_code=0;

	public function redirect($time=3){
		return "<script>setTimeout(function(){ window.location='./'; },".($time*1000).");</script>";
	}

	public function fatal($status_code,$message=false){
		error_reporting(0);
		$this->type="Fatal Error";
		$this->error_code=$status_code;
		$this->type_code=0;
		if($message!=false){$this->detail=$message;} else{ $this->detail=$e[$status_code]; }
		include("modules/error.list.php");
		$title = $this->title=$e[$status_code];
		if(isset($title)){
			$this->title=$title;
		} else{$this->title="Wrong parameters!";}
		include("libraries/view/error.php");
		exit();
	}
	public function warning($status_code,$message=false){
		error_reporting(0);
		$this->type="Warning";
		$this->error_code=$status_code;
		$this->type_code=1;
		if($message!=false){$this->detail=$message;} else{ $this->detail=$e[$status_code]; }
		include("modules/error.list.php");
		$title = $this->title=$e[$status_code];
		if(isset($title)){
			$this->title=$title;
		} else{$this->title="Wrong parameters!";}
		include("libraries/view/error.php");
		exit();
	}
	public function return_error($text){
		return "<div style='background-color:#FF8B8B;padding:10px;border:1px solid red;color:#730000;margin-top:5px;margin-bottom:5px;'><i class='remove circle icon'></i> {$text}</div>";
	}
	public function return_warning($text){
		return "<div style='background-color:#FFC900;padding:10px;border:1px solid #FF9E00;color:#732F00;margin-top:5px;margin-bottom:5px;'><i class='minus circle icon'></i> {$text}</div>";
	}
	public function return_success($text){
		return "<div style='background-color:#54DE71;padding:10px;border:1px solid #00BB25;color:#002808;margin-top:5px;margin-bottom:5px;'><i class='check circle icon'></i> {$text}</div>";
	}
}
?>
