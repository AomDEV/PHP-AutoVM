<?php
include("modules/aom.error.php");
use aomFramework\error;
if(is_dir("modules")){
	if(!defined('_PATH_AUTOLOAD_')){define("_PATH_AUTOLOAD_","./");}
	if(!isset($_autoload['include'])){
		include(_PATH_AUTOLOAD_ . "modules/aom.database.php");
		include(_PATH_AUTOLOAD_ . "modules/aom.form.php");
		include(_PATH_AUTOLOAD_ . "modules/aom.frontend.php");
		include(_PATH_AUTOLOAD_ . "modules/aom.viewer.php");
	} else{
		foreach($_autoload['include'] as $row){
			if(file_exists("modules/aom.{$row}.php")){
				include(_PATH_AUTOLOAD_ . "modules/aom.{$row}.php");
			} else{ $er=new error();$er->warning("0x002"); }
		}
	}
} else{	die("[E] Include path is not main directory!"); }
?>
