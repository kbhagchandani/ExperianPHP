<?php
namespace Experian;

use Experian\XML;

use Experian\Exceptions\InvalidAuth;
use Experian\Exceptions\Unauthorized;
use Experian\Exceptions\InvalidApp;

class Response{

	private $rawResponse;
	private $responseData;

	public function __construct($psr7Response,$parseXML=false){
		$this->rawResponse=$psr7Response;
		switch($this->rawResponse->getStatusCode()){
			case 200:
				$responseBody=$this->rawResponse->getBody()->getContents();
				if($parseXML){
					$this->responseData=XML::decode($responseBody);
					if(isset($this->responseData['ErrorMessage'])) {
						throw new \Exception($this->responseData['ErrorMessage'],$this->responseData['CompletionCode']);
					}
				} else {
					$this->responseData=$responseBody;
				}
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
	}

	public function getProducts(){
		$Products=$this->responseData['Products'];
		if(!$Products) {
			throw new \Exception("Error getting reports.", 403);			
		}
		return $Products;
	}

	public function __toString(){
		return $responseBody;
	}

}