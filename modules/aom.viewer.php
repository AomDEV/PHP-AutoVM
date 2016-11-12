<?php
namespace aomFramework;
class viewer{
  static $php = null;
  static $viewerLocation = "libraries/view/page/";
  public function __construct(){

  }
  static function load($file_name){
    $realName = str_replace("/","",str_replace("..","",$file_name));
    $viewerLocation = self::$viewerLocation;
    if(file_exists($viewerLocation.$realName)){
      require($viewerLocation."view.include.php");
      include($viewerLocation.$realName);
    } else { return "Not found view file"; }
  }
  static function enabled_eval(){
    $disabled = explode(',', ini_get('disable_functions'));
    return !in_array('eval', $disabled);
  }
  static function display($file_name){
    if(self::enabled_eval()==false){return false;}
    $realName = str_replace("/","",str_replace("..","",$file_name));
    if(file_exists("libraries/view/page/".$realName) and file_exists("libraries/view/page/controller.".$realName)){
      require_once("libraries/view/page/controller.".$realName);
      $toClass = str_replace(".","_",str_replace(".php","",$realName));
      $controller = new $toClass();
      $content = file_get_contents("libraries/view/page/".$realName);
      $readyContent = str_replace("<@","<?php",str_replace("@>","?>",$content));
      preg_match_all("/%([a-zA-Z0-9\[\]\'_]*)%/i",$readyContent,$match,PREG_OFFSET_CAPTURE);
      /*$compileVariable = preg_replace("/%([a-zA-Z0-9]*)%/i", "<?=\${$match[1]}?>", $readyContent);*/
      $replace = array();
      for($i=0;$i<count($match[0]);$i++){
        $search[] = $match[0][$i][0];
        $replace[]="<?=\$".str_replace("%","",$match[0][$i][0])."?>";
      }
      $compileVariable = str_ireplace($search,$replace,$readyContent);
      include("libraries/view/page/view.include.php");
      eval(self::$php);
      eval("?> ".$compileVariable);
    } else{echo "Not found page view!";}
  }
  static function transferToLocalVar($storage){
    self::$php = $storage;
  }
  static function getViewerLocation(){
    return self::$viewerLocation;
  }
  static function set($var=array()){
    $key = array_keys($var);
    $eVal = "";
    for($i=0;$i<count($key);$i++)
    {
      $eVal .= "\${$key[$i]} = \"{$var[$key[$i]]}\";";
    }
    self::transferToLocalVar($eVal);
  }
  static function format($string,$format=array()){
    preg_match_all("/\{([a-zA-Z0-9_]*)\}/",$string,$match);
    $newText = $string;
    foreach($match[0] as $index=>$i){
      if(isset($format[$match[1][$index]])){
        $newText = str_replace($i,$format[$match[1][$index]],$newText);
      }
    }
    return $newText;
  }
}
?>
