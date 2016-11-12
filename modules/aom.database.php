<?php
/** # AOM PHP FRAMEWORK [1.0.0] # **/
namespace aomFramework;
use \PDO;
use aomFramework\error;
class database extends PDO{
	private $host	= null;
	private $user	= null;
	private $pass	= null;
	private $db_name= null;
	public $isConnect = false;
	protected $datab;

	private $sql_cache=null;

	public function __construct($host,$user,$pass,$db_name,$options=array()){
		if($this->isConnect != true){
			if(!is_array($options)){$options=array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'");}
			try {
				$this->datab = new \PDO("mysql:host={$host};dbname={$db_name};charset=utf8", $user, $pass, $options);
				$this->datab->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				$this->datab->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
			}
			catch(\PDOException $e) {
				$this->isConnect = false;
				$er = new error();
				$commentDatabase = iconv("TIS-620","UTF-8",$e->getMessage());
				$er->fatal("0x001", $commentDatabase );
			}
		} //Check is connected?
	}

	public function disconnect(){
		$this->datab = null;
		$this->isConnected = false;
	}

	public function insertRow($query, $params){
		try{
			$stmt = $this->datab->prepare($query);
			$stmt->execute($params);
			}catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	public function getNumber($query,$params){
		$stmt = $this->datab->prepare($query);
		$stmt->execute($params);
		return $stmt->rowCount();
	}

	public function getRow($query, $params=array()){
		try{
			$stmt = $this->datab->prepare($query);
			$stmt->execute($params);
			return $stmt->fetch();
			}catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	public function getRows($query, $params=array()){
		try{
			$stmt = $this->datab->prepare($query);
			$stmt->execute($params);
			return $stmt->fetchAll();
			}catch(PDOException $e){
			throw new Exception($e->getMessage());
		}
	}

	public function select($tbl=null){
		if(!isset($tbl)){return false;}
		$this->sql_cache="SELECT %select% FROM {$tbl}";
		return $this;
	}

	public function only($only){
		if($only==false){$only = "*";}
		if(isset($this->sql_cache)){
			$this->sql_cache = str_replace("%select%",htmlspecialchars(htmlentities($only)),$this->sql_cache);
			return $this;
		} else{ return false; }
	}

	public function find($tbl,$op,$val){
		if(isset($this->sql_cache)){
			$this->sql_cache .= " "."WHERE {$tbl} {$op} '{$val}'";
			return $this;
		} else{ return false; }
	}

	public function andFind($tbl,$op,$val){
		if(isset($this->sql_cache)){
			$this->sql_cache .= " AND {$tbl} {$op} '{$val}'";
			return $this;
		} else{ return false; }
	}

	public function order($priv_key,$from_low=true){
		if(isset($this->sql_cache)){
			if($from_low==false){$o="DESC";}else{$o=null;}
			$this->sql_cache .= " "."ORDER BY {$priv_key} {$o}";
			return $this;
		} else{ return false; }
	}

	public function execute($toString=false){
		$this->sql_cache = str_replace("%select%","*",$this->sql_cache);
		$sql = $this->sql_cache;
		if($toString!=true){return $this->getRows($sql,array());}
		else{return $sql;}
	}

	public function printToTable($rows=array(),$rename_byKey=array()){
		if(isset($rows[0])){
			echo '<table border=1>';
			echo '<tr>';
			foreach(array_keys($rows[0]) as $link=>$row){
				$new_name = $row;
				if(isset($rename_byKey[$row])){$new_name=$rename_byKey[$row];}
				if(isset($rename_byKey["auto"]) and $rename_byKey["auto"]==true){$new_name = ucfirst(str_replace("_"," ",$row));}

				echo '<th><center>'.$new_name.'</center></th>';
			}
			echo '<tr>';
			foreach($rows as $link=>$row){
				echo '<tr>';
				foreach(array_keys($rows[0]) as $link=>$key){
					echo "<td><center>{$row[$key]}</center></td>";
				}
				echo '</tr>';
			}
			echo '</table>';
		} else{return false;}
	}

	public function update($tbl,$ar=array(),$where="uid",$operator="=",$where2="1"){
		if(isset($ar) and isset($tbl) and count($ar)>0){
			$sql = "UPDATE {$tbl} SET ";
			for($i=0;$i<count($ar);$i++){
				$key = array_keys($ar);
				$like= $ar[$key[$i]];
				$sql.= "{$key[$i]} = '{$like}'";
				if($i<count($ar)-1){$sql.=" , ";}
			}
			$sql.=" WHERE {$where} {$operator} '{$where2}'";
			@$this->insertRow($sql,array());
			return true;
		} else{return false;}
	}

	public function insert($tbl,$ar=array()){
		if(isset($ar) and isset($tbl) and count($ar)>0){
			$sql = "INSERT INTO {$tbl} (";
			for($i=0;$i<count($ar);$i++){
				$key = array_keys($ar);
				$like= $ar[$key[$i]];
				$sql.= "{$key[$i]}";
				if($i<count($ar)-1){$sql.=" , ";}
			}
			$sql.= ") VALUES (";
			for($i=0;$i<count($ar);$i++){
				$key = array_keys($ar);
				$like= $ar[$key[$i]];
				$sql.= "'{$like}'";
				if($i<count($ar)-1){$sql.=" , ";}
			}
			$sql.= ");";
			@$this->insertRow($sql,array());
			return true;
		} else{return false;}
	}

	public function query($sql,$arg=array()){
		$posWord = strtolower($sql);
		if(strpos($posWord,"insert")!==false or strpos($posWord,"update")!==false or strpos($posWord,"delete")!==false or strpos($posWord,"drop")!==false){
			$this->insertRow($sql,$arg);
			return true;
		} else{
			$r = $this->getRows($sql,$arg);
			return $r;
		}
	}

	public function getSalt(){
		$bigAlb = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$smlAlb = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
		$number = array("0","1","2","3","4","5","6","7","8","9");
		$saltOrg = array_merge($bigAlb,$smlAlb,$number);
		$getSalt = rand(0,count($saltOrg));
		$salt=null;
		for($i=0;$i<rand(10,18);$i++){
			$salt .= $saltOrg[rand(0,(count($saltOrg)-1))];
		}
		return $salt;
	}

	public function passwordEncrypt($password){
		$hash = '$SHA512$'.md5(md5(hash("sha512",sha1($password)))).'$'.hash("sha512",md5(sha1(md5(base64_encode($password)))));
		return $hash;
	}

	public function generate_access_token($uid){
		$access_token = null;
		$aT = array("uid"=>$uid,"last_create"=>time(),"signature"=>hash("sha256",date("dmY-his").rand(100000000000,999999999999)));
		$access_token = json_encode($aT);
		$access_token = hash("sha256",time().$this->getSalt().base64_encode($access_token));
		return $access_token;
	}

	public function encryptText($txt,$private_key){
		$aes = new \Crypt_AES();
		$aes->setKey($private_key);
		$encrypted = $aes->encrypt($txt);
		return bin2hex($encrypted);
	}

	public function decryptText($encrypted,$private_key){
		$aes = new \Crypt_AES();
		$aes->setKey($private_key);
		$decrypted = $aes->decrypt(hex2bin($encrypted));
		return ($decrypted);
	}

	public function login($tbl,$likeBoard=array(),$and_or="AND"){ //login("login_form",array("user_name"=>"username","password"=>encode("Password123")));
		if(isset($likeBoard) && isset($tbl) && count($likeBoard)>0){
			$sql = "SELECT * FROM {$tbl} WHERE ";
			for($i=0;$i<count($likeBoard);$i++){
				$key = array_keys($likeBoard);
				$like= $likeBoard[$key[$i]];
				$sql.= "{$key[$i]} = '{$like}'";
				if($i<count($likeBoard)-1){$sql.=" {$and_or} ";}
			}
			$count = $this->getNumber($sql,array());
			$getInfo = $this->getRows($sql,array());
			if($count>=1){
				return array("status"=>true,"info"=>$getInfo);
			}else{
				return array("status"=>false);
			}
		} else{
			return array("status"=>false);
		}
	}

}
?>
