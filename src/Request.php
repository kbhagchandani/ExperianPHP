<?php
namespace Experian;

use GuzzleHttp\Client;
use Doctrine\Common\Inflector\Inflector;

use Experian\Response;
use Experian\XML;

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
		$ecalCache=sys_get_temp_dir().'/ecal.cache';
		$key = substr(sha1($this->config['username'], true), 0, 16);
		if(file_exists($ecalCache)){
			$fp=fopen($ecalCache,"rb");
			$cacheData=openssl_decrypt(fgets($fp),'blowfish',$key,OPENSSL_RAW_DATA);
			fclose($fp);
			$cacheData=json_decode($cacheData,TRUE);
			if(is_array($cacheData) && (intVal(floor((time()-$cacheData['time'])/86400))<1)){
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
		Validation::isValidExperianURL($this->ecalURL);
		$fp=fopen($ecalCache,"wb");
		fputs($fp,openssl_encrypt(json_encode([
						'url'=>$this->ecalURL,
						'time'=>time()
					]),'blowfish',$key,OPENSSL_RAW_DATA));
		fclose($fp);
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
		$response=new Response($response,true);
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

	public function resetPassord(){
		$response = $this->client->request('POST',"https://ss3.experian.com/securecontrol/reset/passwordreset",[
			'http_errors' => false,
			'verify' => true,
			// 'debug'=> true,
			'auth' => [$this->config['username'], $this->config['password']],
			'form_params' => [
				"command"=>"requestnewpassword",
				"application"=>"netconnect"
			]
		]);
		$response=new Response($response);
		$newPassword=trim($response);
		
		$response = $this->client->request('POST',"https://ss3.experian.com/securecontrol/reset/passwordreset",[
			'http_errors' => false,
			'verify' => true,
			// 'debug'=> true,
			'auth' => [$this->config['username'], $this->config['password']],
			'form_params' => [
				"newpassword"=>$newPassword,
				"command"=>"resetpassword",
				"application"=>"netconnect"
			]
		]);
		$response=new Response($response);
		if($response!="SUCCESS")
			throw new \Exception($response);

		return $newPassword;
	}
}
