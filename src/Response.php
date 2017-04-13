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
	}

	public function getProoducts(){
		return $responseData;
	}

}