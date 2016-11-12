<?php
namespace aomFramework;
class frontend{
	public function load($path){
		$file = explode(".",$path);
		$ext  = $file[count($file)-1];
		if($ext=="css"){
			echo "<link href=\"{$path}\" rel=\"stylesheet\" type=\"text/css\" />";
		} else{
			echo "<script src=\"{$path}\"></script>";
		}
	}
	public function title($title){
		echo '<title>'.$title.'</title>';
	}
	public function start_html(){
		echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
	}
	public function open_body(){
		echo '</head><body>';
	}
	public function end_html(){
		echo '</body></html>';
	}
}
?>
