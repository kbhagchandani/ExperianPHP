<?php
namespace Experian;

use GuzzleHttp\Client;

use Experian\XML;
use Experian\Response;

use Experian\PreQualificationReport;

use Experian\Exceptions\InvalidAuth;
use Experian\Exceptions\Unauthorized;
use Experian\Exceptions\InvalidApp;

class Experian {
	private $client;

	private $config;

	private $data=[];

	private $addOns=null;

	public function __construct($config){
		$this->config=$config;
		$this->client = new Client();
	}

	public function set($key,$value){
		$this->data[$key]=$value;
	}

	private function getResponse($products){
		$request=[
			'NetConnectRequest'=>[
				'attributes'=>[
					'xmlns'=>'http://www.experian.com/NetConnect',
					'xmlns:xsi'=>'http://www.w3.org/2001/XMLSchema instance'
				],
				'EAI'=>$this->config['EAI'],
				'DBHost'=>$this->config['DBHost'],
				'Request'=>[
					'attributes'=>[
						'xmlns'=>'http://www.experian.com/WebDelivery'
					],
					'Products'=>$products
				]
			]
		];
		$xml=XML::encode($request);
		$response = $this->client->request('POST', 'http://localhost/test.php', [
			'http_errors' => false,
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

	public function getPreQualificationReport(){
		$requestData=PreQualificationReport::prepareRequestData($this->getARFRequest());
		$response=$this->getResponse($requestData);
		return PreQualificationReport::extractReport($response);
	}

	public function getARFRequest(){
		$requestData=$this->data;
		$requestData['Vendor']=$this->config['Vendor'];
		$requestData['Subscriber']=$this->config['Subscriber'];
		if(is_array($this->addOns)){
			$requestData['AddOns']=[];
			foreach($this->addOns as $addOn => $value){
				$requestData['AddOns'][$addOn]=$value;
			}
		}
		return $requestData;
	}
}
