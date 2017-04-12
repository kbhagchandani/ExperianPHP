<?php
namespace Experian;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Experian\PreQualificationReport;

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

	function xmlEncode($mixed, $domElement=null, $DOMDocument=null) {
		if (is_null($DOMDocument)) {
			$DOMDocument =new \DOMDocument;
			$DOMDocument->formatOutput = true;
			$this->xmlEncode($mixed, $DOMDocument, $DOMDocument);
			return $DOMDocument->saveXML();
		}
		else {
			// To cope with embedded objects 
			if (is_object($mixed)) {
			  $mixed = get_object_vars($mixed);
			}
			if (is_array($mixed)) {
				foreach ($mixed as $index => $mixedElement) {
					if (is_int($index)) {
						if ($index === 0) {
							$node = $domElement;
						}
						else {
							$node = $DOMDocument->createElement($domElement->tagName);
							$domElement->parentNode->appendChild($node);
						}
					}
					else {
						$plural = $DOMDocument->createElement($index);
						$domElement->appendChild($plural);
						$node = $plural;
						if(isset($mixedElement['attributes'])){
							$attributes=$mixedElement['attributes'];
							foreach($attributes as $attribute => $value){
								$plural->setAttribute($attribute,$value);
							}
							unset($mixedElement['attributes']);
						}
					}

					$this->xmlEncode($mixedElement, $node, $DOMDocument);
				}
			}
			else {
				$mixed = is_bool($mixed) ? ($mixed ? 'true' : 'false') : $mixed;
				$domElement->appendChild($DOMDocument->createTextNode($mixed));
			}
		}
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
		$xml=$this->xmlEncode($request);
		$request=urlencode(preg_replace('~>\s*\n\s*<~', '><', $xml));
		return $request;
	}

	public function getPreQualificationReport(){
		$requestData=PreQualificationReport::prepareRequestData($this->getARFRequest());
		$response=$this->getResponse($requestData);
		return $response;
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
