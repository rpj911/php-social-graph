# PHP Google Social Graph Interface, v.0.0.1
# Author: Paolo Mainardi, Twinbit http://www.twinbit.it ( paolomainardi at gmail )
# License: GNU General Public License v3
# http://www.gnu.org/licenses/gpl.html
# URL: http://code.google.com/p/php-social-graph/

<?php
require_once 'HTTP/Client.php';

class GoogleSocialchart
{
	private $HOST = "http://socialgraph.apis.google.com";
	private $OPT_KEYS = array('edi','edo','fme','pretty','callback','sgn');
	private $DEFAULT_OPTIONS = array(
								'edi' => TRUE, 
								'edo' => TRUE,
								'fme' => TRUE,
								'pretty' => 0,
								'callback' => NULL,
								'sgn' => NULL);

	private $urls;
	
	public function jsonDecode($content, $assoc = FALSE)
	{
		if (!function_exists('json_decode') )
		{
	       	require_once 'lib/JSON.php';
	        if ( $assoc )
	        {
	        	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	        } 
	        else 
	        {
	        	$json = new Services_JSON;
	        }
	        return $json->decode($content);
	    }
	    else
	    {
	    	if ($assoc)
	    	{
	    		return json_decode($content,TRUE);
	    	}
	    	else
	    	{
	    		return json_decode($content);
	    	}
	    }
	    
	}
	
	
	public function jsonEncode($content)
	{
		if ( !function_exists('json_encode') )
		{
		    require_once 'lib/JSON.php';
		    $json = new Services_JSON;      
		    return $json->encode($content);
		}
		else
		{
			json_encode($content);
		}
	}
	
	
	public function __construct($urls)
	{
		$this->urls = $urls;
	}
	
	
	public function query($options = array())
	{
		//$qs = array();
		
		if (count($options) == 0)
		{
			$options = $this->DEFAULT_OPTIONS;
		}
		
		if (is_string($this->urls))
		{
			$qs['q'] = urlencode($this->urls);
		}
		
		if (is_array($this->urls))
		{
			foreach ($this->urls as $k => $v)
			{
				$qs_array[] = urlencode($v);
			}
			$qs['q'] = implode(",",$qs_array);
		}
		
		$query = array_merge($qs,$options);
		
		$query_string = "";
		foreach ($query as $k => $v)
		{
			$query_string[] = $k."=".$v;
		}
		
		$query_string = implode("&",$query_string);
		
		$path = "/lookup?";
		
		$http = new HTTP_Client();
		$res = $http->get($this->HOST.$path,$query_string);
		if ($res == '200')
		{
			$json = $http->currentResponse('body');
			$json = $json['body'];
			return $result = $this->jsonDecode($json, 1);
		}
	}
	
	
	public function referred_to_as($relationship = array())
	{
		return $this->relationship_map($relationship,'nodes_referenced_by');
	}
	public function refers_to_as($relationship = array())
	{
		return $this->relationship_map($relationship,'nodes_referenced');
	}
	
	public function mutual_reference_as($relationship = array())
	{
		return array_unique(array_merge($this->relationship_map($relationship,'nodes_referenced_by')));
	}
	
	private function relationship_map($relationships, $key)
	{
		$results = $this->query();
		$nodes = $results['nodes'];
		
		$nodeRet = array();
		foreach ($nodes as $url => $refs)
		{
			
			$relations = $refs[$key];
			foreach ($relations as $relUrl => $val)
			{
				$types = $val['types'];	
				foreach ($relationships as $r)
				{
					if (in_array($r,$types))
					{
						$nodeRet[] = $relUrl;
					}	
				}
			}
		}
		return array_unique($nodeRet);
	}
	
	private function array_equal($a, $b) 
	{
		return (is_array($a) && is_array($b) && array_diff($a, $b) === array_diff($b, $a));
	}
	
}
	

$a = new GoogleSocialchart(array('http://www.fullo.net'));
//print_r($a->query());
//$test = $a->referred_to_as(array('met'));
//$test = $a->refers_to_as(array('met','friend', 'acquaintance'));
$test = $a->mutual_reference_as(array('me'));
print_r($test);

?>