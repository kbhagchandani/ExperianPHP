<?php
namespace Experian;

use GuzzleHttp\Client;
use Doctrine\Common\Inflector\Inflector;

use Experian\Response;
use Experian\XML;

use Experian\Exceptions\InvalidAuth;
use Experian\Exceptions\Unauthorized;
use Experian\Exceptions\InvalidApp;

class Request{

	private $client;
	private $config;
	private $rawResponse;
	private $responseData;
	private $ecalURL;

	public function __construct($config){
		$this->config=$config;
		$this->client = new Client();
		$this->getECALUrl();
	}

	private function getECALUrl(){
		$ecalCache=sys_get_temp_dir().'ecal.cache';
		if(file_exists($ecalCache)){
			$cacheData=json_decode(base64_decode(file_get_contents($ecalCache)));
			if(is_object($cacheData) && (intVal(floor((time()-$cacheData['time'])/86400))<1)){
				$this->ecalURL=$cacheData['url'];
				return true;
			}
		}
		$response = $this->client->request('GET',"http://www.experian.com/lookupServlet1",[
					'query'=>[
						'lookupServiceName'=>'AccessPoint',
						'lookupServiceVersion'=>'1.0',
						'serviceName'=>$this->config['service_name'],
						'serviceVersion'=>'2.0',
						'responseType'=>'text/plain'
					]
				]);
		$this->ecalURL=trim($response->getBody()->getContents());
		file_put_contents($ecalCache,base64_encode(json_encode([
						'url'=>$this->ecalURL,
						'time'=>time()
					])));
	}

	public function getARFResponse($products){
		$request=[
			'NetConnectRequest'=>[
				'attributes'=>[
					'xmlns'=>'http://www.experian.com/NetConnect',
					'xmlns:xsi'=>'http://www.w3.org/2001/XMLSchema instance'
				],
				'EAI'=>$this->config['eai'],
				'DBHost'=>$this->config['db_host'],
				'Request'=>[
					'attributes'=>[
						'xmlns'=>'http://www.experian.com/WebDelivery'
					],
					'Products'=>$products
				]
			]
		];
		$xml=XML::encode($request,null,null,false);
		$response = $this->client->request('POST', $this->ecalURL, [
			'http_errors' => false,
			'verify' => true,
			// 'debug'=> true,
			'auth' => [$this->config['username'], $this->config['password']],
			'form_params' => [
				'NETCONNECT_TRANSACTION' => $xml
			]
		]);
		switch($response->getStatusCode()){
			case 200:
				$response=new Response($response);
			break;
			case 302:
				throw new InvalidAuth;
			break;
			case 403:
				throw new Unauthorized;
			break;
			case 404:
				throw new InvalidApp;
			break;
			default:
				throw new \Exception($response->getReasonPhrase(),$response->getStatusCode());
		}

		return $response;
	}

	public function getARFRequestParameters($configProperties=[]){
		$requestData=[];
		foreach($configProperties as $property){
			$key=Inflector::classify($property);
			if(is_array($this->config[$property])){
				$requestData[$key]=[];
				foreach($this->config[$property] as $subKey => $value){
					$requestData[$key][Inflector::classify($subKey)]=$value;
				}
			} else {
				$requestData[$key]=$this->config[$property];
			}
		}
		// if(is_array($this->addOns)){
		// 	$requestData['AddOns']=[];
		// 	foreach($this->addOns as $addOn => $value){
		// 		$requestData['AddOns'][$addOn]=$value;
		// 	}
		// }
		return $requestData;
	}

}
