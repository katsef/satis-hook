<?php
namespace Satis\Hook;

class Hook extends Base{
    public $status;
    public $ssh_url;
    public $html_url;
    public $get_url;
    public $exception;
    
    function __construct($get = NULL, $post = NULL,$head = NULL) {
        set_time_limit(300);
        Exception::set_error();
		
        $this -> status = false;	
        $this -> get_url = false;
        
        
        try{
            $json=Base::Get($get);
            if ($json)
            {
            if (JSON::isJson($json))
                {
                $json=json_decode($json,true);
                if (isset($json['url']))
                    {
                    $this->get_url=$json['url'];
                    }
                }
                
            }
            
            if ($this->get_url)
            {
                $post=[];
                $post['url']=$this->get_url;
                $post=json_encode($post);
            }
            
            
            if (JSON::isJson($post) && JSON::isJson($head))
            {
            Base::WriteTmp('HEAD',$head);
            Base::WriteTmp('GET',$get);
            Base::WriteTmp('BODY',$post);
            $this -> ssh_url = Base::GetSSH($post);    
            $this -> html_url = Base::GetHTML($post);    
            if ($this -> ssh_url)
                {
                Base::AddRepo($this -> ssh_url);
                $this->status=Base::Satis($this -> ssh_url);
                }
                }else{
                
                throw new Exception('Invalid incoming data format',0,__FILE__,__LINE__);
                }
            
            
            
        }
        catch (\Throwable $e) {    
        $this -> exception = Exception::getException($e);  
} 
    }
    
    
}

?>