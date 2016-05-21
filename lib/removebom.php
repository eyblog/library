<?php
    $basedir = '.';                 //执行目录
    if(php_sapi_name()=='cli'){
        $argv[1]&&$basedir=$argv[1];
      fwrite(STDOUT,"Enter the Y to confirm the implementation in the ".$basedir." directory:\n");
      strtolower(trim(fgets(STDIN)))=='y'||die();
    }else{
        isset($_GET['dir'])&&$basedir = $_GET['dir'];
    }
$auto = 1;
checkdir($basedir);

function checkdir($basedir)
{
    if ($dh = opendir($basedir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                if (!is_dir($basedir . "/" . $file)) {
                    echo "filename: $basedir/$file " . checkBOM("$basedir/$file") . "\n";
                } else {
                    $dirname = $basedir . "/" . $file;
                    checkdir($dirname);
                }
            }
        }
        closedir($dh);
    }
}
function checkBOM($filename)
{
    global $auto;
    $contents   = file_get_contents($filename);
    $charset[1] = substr($contents, 0, 1);
    $charset[2] = substr($contents, 1, 1);
    $charset[3] = substr($contents, 2, 1);
    if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
        if ($auto == 1) {
            $rest = substr($contents, 3);
            rewrite($filename, $rest);
            return colorize("BOM found, automatically removed.","NOTE");
        } else {
            return colorize("BOM found","WARNING");
        }
    } else
        return colorize("BOM Not Found.","WARNING");
}

function rewrite($filename, $data)
{
    $filenum = fopen($filename, "w");
    flock($filenum, LOCK_EX);
    fwrite($filenum, $data);
    fclose($filenum);
}
function colorize($text, $status) {  
 $out = "";  
 switch($status) {  
  case "SUCCESS":  
   $out = "[42m"; //Green background  
   break;  
  case "FAILURE":  
   $out = "[41m"; //Red background  
   break;  
  case "WARNING":  
   $out = "[43m"; //Yellow background  
   break;  
  case "NOTE":  
   $out = "[44m"; //Blue background  
   break;  
  default:  
   throw new Exception("Invalid status: " . $status);  
 }  
 return chr(27) . "$out" . "$text" . chr(27) . "[0m";  
}

?>