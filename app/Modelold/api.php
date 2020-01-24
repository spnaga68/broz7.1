<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use GuzzleHttp\Client;
use Session;
class api extends Model
{
	private $_currency = array();
	public function __construct()
    {
		$this->client = new Client([
			// Base URI is used with relative requests
			'base_uri' => url('/'),
            // 'base_uri' => url('http://127.0.0.1/'),
			// You can set any number of default request options.
			//'timeout'  => 3000.0,
		]);

    }

	public function call_api($post_data=array(),$url="",$method="")
    {
		//print_r($post_data);exit;
		if($method == "GET")
		{
			$response = $this->client->request($method,$url);
		}
		else
		{
			//print_r($this->client);exit;
			$response = $this->client->request($method,$url,$post_data);
		}
		//print_r($post_data);exit;
        $code = $response->getStatusCode(); 
        $body = $response->getBody();
        $response = $body->getContents();
		//print_r($response);exit;
        $response_data = json_decode($response);
        return $response_data;
    }
    
    public function getLocation() 
    {
		return $this->call_api('','api/getlocation/'.getCurrentLang(),'GET');
		
	}
	
	public function get_coperatives()
	{
		$response = $api->call_api($data,'api/get_coperatives',$method);
		$coopratives = $response->response->vendors;
		$cooprative_list[""] = trans("messages.Select cooprative");
		foreach ($coopratives as $cooprative)
		{
			$cooprative_list[$cooprative->outlets_id] = $cooprative->outlet_name;
		}
		return $cooprative_list;
	}

	public function getCity() 
    {
		return $this->call_api('','api/getcity/'.getCurrentLang(),'GET');
		
	}
	
	public function getcountry_select() 
    {
		return $this->call_api('','api/getcountry_select/'.getCurrentLang(),'GET');
		
	}

	public function getFeatureSstore() 
    {
		return $this->call_api('','api/getfeaturesstore/'.getCurrentLang(),'GET');
		
	}

	public function getOffers() 
    {
		return $this->call_api('','api/getoffers_list/'.getCurrentLang(),'GET');
		
	}

	/** get current Language **/
	public  function getCurrency($language_id = '')
	{  
		if($language_id == '')
		{
		$language_id = getAdminCurrentLang();
	    }
		if(!$this->_currency)
		{
			$query = '"currencies_infos"."language_id" = (case when (select count(*) as totalcount from currencies_infos where currencies_infos.language_id = '.$language_id.' and currencies.id = currencies_infos.currency_id) > 0 THEN '.$language_id.' ELSE 1 END)';
		    $currentcurrency = DB::table('currencies')
		                  ->select('currencies_infos.currency_symbol')
		                  ->join('settings','settings.default_currency','=','currencies.id')
		                  ->join('currencies_infos','currencies_infos.currency_id','=','currencies.id')
		                  ->where('active_status', 'A')
		                  ->whereRaw($query)
		                  ->get();
			$this->_currency='';
			if(count($currentcurrency)>0)
			{
				if($currentcurrency[0]->currency_symbol)
				{
					$this->_currency = $currentcurrency[0]->currency_symbol;
				}
			}
		}
		return $this->_currency;
	}

	public function getpromotion() 
    {
    	//print_r("expression");exit();
		return $this->call_api('','api/promotionOffers/','GET');
		
	}

}

