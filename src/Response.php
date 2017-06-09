<?php
namespace Experian;

use Experian\XML;

class Response{

	private $rawResponse;
	private $responseData;

	public function __construct($psr7Response){
		$this->rawResponse=$psr7Response;
		$responseBody=$this->rawResponse->getBody()->getContents();
		$this->responseData=XML::decode($responseBody);
		if(isset($this->responseData['ErrorMessage'])) {
			throw new \Exception($this->responseData['ErrorMessage'],$this->responseData['CompletionCode']);
		}
	}

	public function getProducts(){
		$Products=$this->responseData['Products'];
		if(!$Products) {
			throw new \Exception("Error getting reports.", 403);			
		}
		return $Products;
	}

}