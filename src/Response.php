<?php
namespace Experian;

use Experian\XML;

use Experian\Exceptions\InvalidAuthException;
use Experian\Exceptions\UnauthorizedException;
use Experian\Exceptions\InvalidAppException;

class Response{

	private $rawResponse;
	private $responseData;
	private $log;

	public function __construct($psr7Response,&$log,$parseXML=false,$logIO=false){
		$this->rawResponse=$psr7Response;
		$this->log=$log;
		switch($this->rawResponse->getStatusCode()){
			case 200:
				$responseBody=$this->rawResponse->getBody()->getContents();
				if($parseXML){
					if($logIO){
						$this->log->info("Experian NetConnectTransaction Response : $responseBody");
					}
					$this->responseData=XML::decode($responseBody);
					if(isset($this->responseData['ErrorMessage'])) {
						throw new \Exception($this->responseData['ErrorMessage'],$this->responseData['CompletionCode']);
					}
				} else {
					$this->responseData=$responseBody;
				}
			break;
			case 302:
				throw new InvalidAuthException;
			break;
			case 403:
				throw new UnauthorizedException;
			break;
			case 404:
				throw new InvalidAppException;
			break;
			default:
				throw new \Exception($this->rawResponse->getReasonPhrase(),$this->rawResponse->getStatusCode());
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

	public function getHeader($headerName){
		$header=$this->rawResponse->getHeader($headerName);
		if(is_array($header))
			return $header[0];
		else
			throw new \Exception("Unknown Header : $headerName");
	}

	public function getHeaders(){
		return $this->rawResponse->getHeaders();
	}

}