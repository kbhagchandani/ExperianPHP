<?php
namespace Experian;

use GuzzleHttp\Client;
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

	const CERT_PATH = __DIR__.'/../cert';

	public function __construct($config){
		$this->config=$config;
		$this->client = new Client();
		// $this->getECALUrl();
		$this->ecalURL="https://www.experian.com/netconnect2_0/servlets/NetConnectServlet";
	}

	private function getECALUrl(){
		$response = $this->client->request('GET',"http://www.experian.com/lookupServlet1",[
					'query'=>[
						'lookupServiceName'=>'AccessPoint',
						'lookupServiceVersion'=>'1.0',
						'serviceName'=>$this->config['service_name'],
						'serviceVersion'=>'2.0',
						'responseType'=>'text/plain'
					]
				]);
		$this->ecalURL=$response->getBody()->getContents();
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
		$xml=XML::encode($request);
		$response = $this->client->request('POST', $this->ecalURL, [
			'http_errors' => false,
			'verify' => self::CERT_PATH.'/cacert.pem',
			'auth' => [$this->config['username'], $this->config['password']],
			'form_params' => [
				'NETCONNECT_TRANSACTION' => urlencode(preg_replace('~>\s*\n\s*<~', '><', $xml))
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
				throw new Exception($response->getReasonPhrase(),$response->getStatusCode());
		}

		return $response;
	}

	/**
	 * Install CA certificates extracted from Mozilla
	 */
	public static function installMozillaCACert(){
		$client = new Client();
		$response = $client->request('GET','https://curl.haxx.se/ca/cacert.pem',['verify' => true]);
		if(!file_exists(self::CERT_PATH))
			mkdir(self::CERT_PATH);
		$f=fopen(self::CERT_PATH.'/cacert.pem','wb');
		if(!$f) {
			throw new \Exception('Failed to open file for saving.');
		}
		fwrite($f,$response->getBody()->getContents());
		fclose($f);
	}

}
