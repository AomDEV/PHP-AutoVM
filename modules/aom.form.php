<?php
namespace aomFramework;
use aomFramework\error;
interface formInterface{
    public function open($name,$action);
    public function textbox($type,$name,$special);
}
class form implements formInterface{
	private $form_name = null;
  private $form_token = array();
	private $action = null;
	private $method = "get";
	private $inputHTML = "";
	private $cache_html = array();
	private $inputPost = array();
  private $js_btn = "";
  private $html_js = "";
  private $form_lastCreateToken = array();
	public function open($name="main_frm",$method="get",$action=null,$class=null){
		$this->form_name=$name;
		$this->action=$action;
		$this->method = $method;
		$this->inputPost[$name]["method"]=$method;
    $this->form_class=$class;
		return $this;
	}

	public function save(){
    $gen_js = "<script>{$this->js_btn}</script>";
    $this->html_js = $gen_js;
    if(!isset($this->form_token[$this->form_name]) or $this->form_lastCreateToken[$this->form_name]!=date("dmYh")){
      $this->form_lastCreateToken[$this->form_name] = date("dmYh");
      $this->form_token[$this->form_name] = md5(hash("sha256",date("dmYh")."$"));
    }
    $html = "";
		$html .= "<form method='{$this->method}' class='{$this->form_class}' action='{$this->action}' name='{$this->form_name}'>";
		$html .= "<input type='hidden' name='form_name' value='{$this->form_name}' />";
		$html .= "<input type='hidden' name='token' value='".@$this->form_token[$this->form_name]."' />";
		$html .= $this->inputHTML;
		$html .= "</form>";
    $html .= $this->html_js;
		$this->cache_html[$this->form_name]=$html;
		$this->form_name=null;
		$this->action=null;
		$this->method="get";
		$this->inputHTML="";
		return true;
	}

	public function render($formName){
		if(isset($this->cache_html[$formName])){
			return $this->cache_html[$formName];
		} else {return false;}
	}

	public function label($text){
		$html = "<label>{$text}</label>";
		$this->inputHTML .= $html;
		return $this;
	}

  public function select($value=array(),$name="selector",$special="",$validate="encrypt_text"){
    if(isset($value) and is_array($value) and count($value)>0){
      $textcomp=explode(",",$special);
      $comp="";
  		foreach($textcomp as $row){
  			if(preg_match("/\[(.*)\]/i",$row)){
  				preg_match("/\[(.*)\]/i",$row,$match);
  				$compKey = str_replace("[{$match[1]}]","",$row);
  				$comp .= "{$compKey}='{$match[1]}' ";
  			} else{
  				$comp .= "{$row}={$row} ";
  			}
  		}
      $html="<select name='{$name}' id='{$name}' {$comp} data-create='9framework'>";
      for($i=0;$i<count($value);$i++){
        $key = array_keys($value)[$i];
        $html .= "<option value='{$key}'>{$value[$key]}</option>";
      }
      $html.="</select>";
      $html.="<script>$('#{$name}').dropdown();</script>";
      $this->inputHTML.=$html;
      $this->inputPost[$this->form_name]["input"][]=$name;
  		$this->inputPost[$this->form_name]["validate"][]=$validate;
      return $this;
    } else{return false;}
  }

	public function textbox($type="text",$name="textbox",$placeholder="Textbox",$special="",$validate="name"){
		$texttype=array("text"=>"text","pass"=>"password","email"=>"email");
		$textcomp=explode(",",$special);
		$comp = "";
		foreach($textcomp as $row){
			if(preg_match("/\[(.*)\]/i",$row)){
				preg_match("/\[(.*)\]/i",$row,$match);
				$compKey = str_replace("[{$match[1]}]","",$row);
				$comp .= "{$compKey}='{$match[1]}' ";
			} else{
				$comp .= "{$row}={$row} ";
			}
		}
		$html = "<input type='{$texttype[$type]}' placeholder='{$placeholder}' name='{$name}' {$comp} />";
		$this->inputHTML.=$html;
		$this->inputPost[$this->form_name]["input"][]=$name;
		$this->inputPost[$this->form_name]["validate"][]=$validate;
		return $this;
	}

  public function hidden($value,$name,$id=null){
    $time = time()*time()+time()/rand(2,9);
    $this->inputHTML.="<input type='hidden' value='{$value}' name='{$name}' id='{$id}' value='{$time}' />";
    return $this;
  }

	public function button($type="submit",$value="Submit",$class="",$url=false,$has_confim=false,$more_tag=null){
		$btn = array("submit"=>0,"button"=>1);
		if(isset($btn[$type])){
      $onclick="data-gen='".time()."'";
      if($url!=false){$id=substr(md5(rand(0,9)),0,8);$onclick .= " id='btn-".$id."'";}
      if($has_confim==true){$onclick.=" onclick=\"return confirm('Are you sure?');\"";}
			$html = "<button type='{$type}' class='{$class}' {$onclick} {$more_tag}>{$value}</button>";
			$this->inputHTML .= $html;
      if(isset($id)){$this->js_btn .= "$(\"#btn-".$id."\").click(function(){window.location='{$url}';});";}
			return $this;
		} else{ return false; }
	}

	public function enter(){
		$this->inputHTML.="<br />";
		return $this;
	}

	public function classCrop($class,$id=null){
		$this->inputHTML.="<div class=\"{$class}\" id='{$id}'>";
		return $this;
	}

  public function executeJS($script){
    $return = "<script>/*Generated by aomFramework*/{$script}</script>";
    echo $return;
  }

  public function center(){
    $this->inputHTML.="<center>";
    return $this;
  }

  public function string($string){
    $this->inputHTML.=$string;
    return $this;
  }

  public function h3($class){
    $this->inputHTML.="<h3 class='{$class}'>";
    return $this;
  }

  public function endh3(){
    $this->inputHTML.="</h3>";
    return $this;
  }

  public function i($class,$id=null){
    $this->inputHTML.="<i id='{$id}' class='{$class}'></i>";
    return $this;
  }

  public function right(){
    $this->inputHTML.="<div align=right>";
    return $this;
  }

  public function endRight(){
    $this->inputHTML.="</div>";
    return $this;
  }

  public function endCenter(){
    $this->inputHTML.="</center>";
    return $this;
  }

	public function endCrop(){
		$this->inputHTML.="</div>";
		return $this;
	}

	private $validate_html = "";
	private $validate_failed = 0;
	private $validate_passed = 0;

	public function validate($formName){
		$er = new error();
		if(isset($this->inputPost[$formName])){
			if($this->inputPost[$formName]["method"]=="post"){$method=$_POST;}else{$method=$_GET;}
			if(isset($method["form_name"]) and $method["form_name"]==$formName){
      if(isset($this->form_token[$formName])){
				$failed=0;
				$passed=0;
				$validateId=array(
					"name"=>"/^[a-zA-Z0-9]*$/",
					"email"=>"/^[^0-9][_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",
					"full_name"=>"/^[a-zA-Z0-9 ]*$/",
					"int"=>"/^[0-9]*$/",
					"float"=>"/^-?(?:\d+|\d*\.\d+)$/",
          "currency"=>"/^\d*(?:\.\d+)?/i",
					"url"=>"/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/",
					"host"=>"/^([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/",
          "phone"=>"/^(09|08|06)[0-9]{8}$/",
          "date_time"=>"/(\d{2}-\d{2}-\d{4} \d{2}:\d{2})/i",
          "encrypt_text"=>"/[a-z0-9A-Z+=]*/i",
          "card_exp"=>"/^[1-12]{2}\/[0-9]{4}$/i",
          "address"=>"/^[a-zA-Z0-9\. ]$/",
					"ip"=>"/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/");

        foreach($this->inputPost[$formName]["input"] as $link=>$row){
					$validate = $this->inputPost[$formName]["validate"][$link];

          if($validate=="currency"){
            $value0=str_replace("$","",$method[$row]);
            $value1=str_replace("฿","",$value0);
            $value2=str_replace(",","",$value1);
            $value=floatval($value2);
            if($value<0 or strlen($value)>9){$failed++;}
          } else{$value = $method[$row];}

					if(preg_match($validateId[$validate],$value)==true){
						$passed++;
					} else{ $failed++; }
				}
				$this->validate_failed = $failed;
				$this->validate_passed = $passed;
				if($passed+$failed==count($this->inputPost[$formName]["input"])){
					if($failed>0){
						$this->validate_html = $er->return_warning("Data formats is not valid!");
						return $this;
					} else{
						$this->validate_html = $er->return_success("Successful!");
						return $this;
					}
				} else{$this->validate_failed=-1;$er->warning("0x003","Not found parameter in request process!");}
      } else{$this->validate_failed=-1;$er->warning("0x003","Not found parameter in request process!");}
			} else{$this->validate_failed=-1;return $this;}
		} else{$this->validate_failed=-1;return $this;}
	}

	public function finish($bool=false){
		if(isset($this->validate_html) and $bool==false){
			echo $this->validate_html;
			return false;
		} else if($bool==3 and $this->validate_failed>0){ //Show only failed message
			echo $this->validate_html;
			return false;
		} else if($this->validate_failed>0){
			return false;
		} else if($this->validate_failed<0){
			return false;
		} else{
			return true;
		}
	}

  public function formValue($formName,$key){
    if($this->inputPost[$formName]["method"]=="post"){$method=$_POST;}else{$method=$_GET;}
    $inputList = $this->inputPost[$formName]["input"];
    for($i=0;$i<count($inputList);$i++){
      if($inputList[$i]==$key){
        return $method[$key];
      }
    }
  }

  public function js_redirect($second_time=3,$url){
    echo "<script>setTimeout(function(){window.location='{$url}';},{$second_time});</script>";
  }

  public function get_session($session_var,$want_to_get){
    return $session_var["user_info"][$want_to_get];
  }
  public function generateTXID(){
    $txid = "A".date("d")."O".date("m")."M".date("Y").strtoupper(uniqid(mt_rand(),true));
    return $txid;
  }
  public function shortTXID($txid){
    $regex = "/A(.*)\O(.*)\M2016/";
    preg_match_all($regex, $txid, $match, PREG_SET_ORDER, 0);
    return "A".str_replace($match[0],"",$txid);
  }
  public function convertCurrencyToFloat($currency="฿1,399.01"){
    $findPrefix = array("฿","$");
    $replacePrefix = array("","");
    $removePreFix = str_ireplace($findPrefix,$replacePrefix,$currency);
    $removeComma = str_replace(",","",$removePreFix);
    $completeFloat = floatval($removeComma);
    return $completeFloat;
  }
  public function timestampToDatetime($timestamp){
    return date("d/m/Y H:i:s",$timestamp);
  }
  public function timestampToThaiDate($timestamp){
    $day = array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");
    $month = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    $mnth = array("ม.ค.","ก.พ.","มี.ค.","เม.ย","พ.ค.","มิ.ย","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    return date("d",$timestamp)." ".$mnth[intval(date("m",$timestamp))-1]." ".date("Y",$timestamp).", ".date("H:i:s",$timestamp);
  }
  public function randomName($length=8,$includeInt=false){
    $alphabet = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
    $alpInt = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",0,1,2,3,4,5,6,7,8,9);
    $name = null;
    for($i=0;$i<=$length;$i++){
      if($includeInt==true){
        $name.=$alpInt[rand(0,count($alpInt)-1)];
      } else{
        $name.=$alphabet[rand(0,count($alphabet)-1)];
      }
    }
    return $name;
  }
}
class api{
  public function validate($session,$request,$requestKey=array()){
    if(isset($session["user_info"]) and $session["user_info"]["is_login"]==true){
      if(isset($request) and isset($request["token"])){
        if($request["token"]==$session["user_info"]["access_token"]){
          for($i=0;$i<count($requestKey);$i++){
            $key = ($requestKey[$i]);
            if(!isset($request[$key])){return false;}
          }
          return true;
        } else{return false;}
      } else{return false;}
    } else{return false;}
  }
}
?>
