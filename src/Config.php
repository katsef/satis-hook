<?php
namespace Webazon\SatisHook;

class Config
{
    
 public static function Path()
 {
 $res = new \stdClass();
 $path = realpath('../').'/';   
 $root = $_SERVER['DOCUMENT_ROOT'].'/';
 $res->path = $path;   
 $res->root = $root;
 $res->bin = $path.'bin/satis';
 $res->config = $path.'satis.json';
 $res->output = $path.'repo';
 $res->home = $home = getenv("HOME").'/';
 
 return $res;
 }
    
    
    
    
    
    
}

?>