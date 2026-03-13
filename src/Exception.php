<?php
namespace Satis\Hook;

class Exception extends \Exception {
public function __construct($message, $errorLevel = 0, $errorFile = '', $errorLine = 0) {
      parent::__construct($message, $errorLevel);
      $this->file = $errorFile;
      $this->line = $errorLine;
         
      
   }

static function set_error()
{
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }
    
    throw new Exception($errstr, $errno, $errfile, $errline);
});	
}
    
static function getException($e)
{ 
$res = new \stdClass();   
$res -> message = $e->getMessage();
$res -> message = str_replace("'","`",$res -> message);
$res -> file = $e->getFile();
$res -> line = $e->getLine();
$res -> code = $e->getCode();  
if (JSON::isJSON($res->message))
    {
    $json=json_decode($res->message,true);
    $res->class=$json['class'];
    $res->function=$json['function'];
    $res->message=$json['message'];
    $res->line=$json['line'];
    $res->file=$json['file'];
    }
  
    
return $res;
}
    
    
	
}



?>