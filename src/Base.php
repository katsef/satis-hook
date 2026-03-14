<?php
namespace Webazon\SatisHook;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class Base
{
    public $flag;
    public $last_error;
    public $filelog;
    
        protected function WriteTmp($name = NULL, $body = NULL)
    {
        if (!is_dir(sys_get_temp_dir() . '/satis'))
        {
            mkdir(sys_get_temp_dir() . '/satis', 0777, true);
        }
        if (!is_dir(sys_get_temp_dir() . '/satis/requests'))
        {
            mkdir(sys_get_temp_dir() . '/satis/requests', 0777, true);
        }
        $temp_file = sys_get_temp_dir() . '/satis/requests/' . $name . '_' . date('Y-m-d H:i:s', time()) . '.json';
        $fp = fopen($temp_file, 'w');
        fwrite($fp, $body);
        fclose($fp);
    }

    protected function GetSSH($json)
    {
        $json = json_decode($json, true);
        if (isset($json['repository']['ssh_url']))
        {
            return $json['repository']['ssh_url'];
        }
        else
        {
            if (isset($json['project']['git_ssh_url']))
            {
                return $json['project']['git_ssh_url'];
            }
            else
            {
             if (isset($json['url']))
                {
                return $json['url'];
                }
                else{   
                    return false;
                    }
            }
        }

    }

    protected function GetHTML($json)
    {
        $json = json_decode($json, true);
        if (isset($json['repository']['html_url']))
        {
            return $json['repository']['html_url'];
        }
        else
        {
            if (isset($json['project']['git_http_url']))
            {
                return $json['project']['git_http_url'];
            }
            else
            {
                return false;
            }
        }

    }

    protected function AddRepo($ssh_url)
    {
        $res = false;
        $file = Config::Path()->config;
        $satis = json_decode(file_get_contents($file) , true);
        $flag = true;
        for ($i = 0;$i < count($satis['repositories']);$i++)
        {
            if ($satis['repositories'][$i]['url'] == $ssh_url)
            {
                $flag = false;
                break;
            }
        }
        if ($flag)
        {
            $a['type'] = 'vcs';
            $a['url'] = $ssh_url;
            array_push($satis['repositories'], $a);
            $fp = fopen($file, 'w');
            fwrite($fp, json_encode($satis, JSON_PRETTY_PRINT));
            fclose($fp);
            $res = true;
        }
        return $res;
    }
    
    protected function DelRepo($ssh_url)
    {
    $res=false;
    $file = Config::Path()->config;
    $satis = json_decode(file_get_contents($file) , true);
    $repo=[];
    for ($i = 0;$i < count($satis['repositories']);$i++)
        {
            if ($satis['repositories'][$i]['url'] !== $ssh_url)
            {
            array_push($repo,$satis['repositories'][$i]);
            }else{$res=true;}
        
        }    
    $satis['repositories']=$repo;
    
    
    
      $fp = fopen($file, 'w');
            fwrite($fp, json_encode($satis, JSON_PRETTY_PRINT));
            fclose($fp);  
    
    return $res;
    }
    
    protected function Get($get=NULL)
        {
        $res=false;
        $a=explode('&',$get);
        if (count($a)>0)
            {
            $json=[];
            
            {
            for ($i=0;$i<count($a);$i++)
                {
                $tm=explode('=',$a[$i]);
                if (count($tm)==2)
                {
                $json[$tm[0]]=$tm[1];
                }
            }
            return json_encode($json);
            }
            }
        
        
        return $res;
        }
    
    protected function Satis($url)
    {
        $this->flag = false;
        $this->last_error = '';
        if (!is_dir(Config::Path()->home.'.log'))
        {
            mkdir(Config::Path()->home.'.log', 0755, true);
        }
        if (!is_dir(Config::Path()->home.'.log/satis'))
        {
            mkdir(Config::Path()->home.'.log/satis', 0755, true);
        }
        $filename=Config::Path()->home.'.log/satis/'.date('Y-m-d_H:i:s', time()).'.log';
        $fp=fopen($filename,'w');
        fclose($fp);
        $this->filelog=$filename;
        $process = new Process(['php', Config::Path()->bin, 'build', '--repository-url', $url, Config::Path()->config, Config::Path()->output]);
        
        $exitCode = $process->run(function ($type, $buffer)
        {
            file_put_contents($this->filelog, $type.' => '.$buffer, FILE_APPEND);
            if (Process::OUT === $type)
            {
                $str = preg_replace('/[^a-z0-9 ]/i', '', $buffer);
                if ($str === 'Writing web view')
                {
                    $this->flag = true;
                    $this->last_error = NULL;
                }
            }
            else
            {
                $this->last_error = preg_replace('/[^a-z0-9@.\s\/ ]/i', '', $buffer);

            }

        });
        
        if (!$this->flag)
        {
            Self::DelRepo($url);
            throw new Exception($this->last_error, 0, __FILE__, __LINE__);
        }
        return $this->flag;
    }
  
    
}

?>
