<?php

namespace Experian;

use Experian\Exceptions\InvalidDataTypeException;
use Experian\Exceptions\CombinedLengthExceedsPermittedLimitException;
use Experian\Exceptions\FieldLengthExceedsPermittedLimitException;
use Experian\Exceptions\MissingMandatoryFieldException;
use Experian\Exceptions\{InvalidHostUrlException,InvalidSSLException};

class Validation {

	public static function validate($schema,$data){
		$combinedTextLength=0;
		foreach($schema as $node => $validationProperty) {
			if(isset($data[$node]) && !empty($data[$node])) {
				if(isset($validationProperty['ChildNodes'])) {
					if(!is_array($data[$node])) {
						throw new InvalidDataTypeException($node,'Associative Array');
					}
					$childNodesCombinedTextLength=self::validate($validationProperty['ChildNodes'],$data[$node]);
					if(isset($validationProperty['CombinedChildTextLengthLimit']) && $validationProperty['CombinedChildTextLengthLimit']<$childNodesCombinedTextLength) {
						throw new CombinedLengthExceedsPermittedLimitException($node,$validationProperty['CombinedChildTextLengthLimit']);
					}
				} else {
					$length=strlen($data[$node]);
					if(isset($validationProperty['MaxLength']) && $validationProperty['MaxLength']<$length){
						throw new FieldLengthExceedsPermittedLimitException($node,$validationProperty['MaxLength']);	
					}
					$combinedTextLength+=$length;
				}
			} else if(isset($validationProperty['Required']) && $validationProperty['Required']) {
				throw new MissingMandatoryFieldException($node);
			}
		}
		return $combinedTextLength;
	}

	public static function isValidExperianURL($url,$log=null){
		$urlComponents=parse_url($url);
		$host=explode('.',$urlComponents["host"]);
		$masterHost=$host[count($host)-2].'.'.end($host);

		if(count($host)<3 || "$masterHost"!=="experian.com" || $urlComponents["scheme"]!=="https"){
			if($log)
				$log->error("InvalidHostUrl Exception: $url is an invalid ECAL transaction URL.");
			throw new InvalidHostUrlException();
		}
		if(!isset($urlComponents["port"]))
			$urlComponents["port"]=443;

		$g = stream_context_create (array("ssl" => ["capture_peer_cert" => true,"verify_peer"=>false,'verify_peer_name'=>false]));
		$r = stream_socket_client("ssl://{$urlComponents["host"]}:{$urlComponents["port"]}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $g);
		$cont = stream_context_get_params($r);
		$certInfo=openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);

		if($certInfo['subject']['CN']!==$urlComponents["host"]){
			if($log)
				$log->error("InvalidSSL Exception: $url has invalid hostname {$certInfo['subject']['CN']} in certificate.");
			throw new InvalidSSLException();
		}
		if($certInfo['validTo_time_t']<time()) {
			if($log)
				$log->error("InvalidSSL Exception: $url has expired certificate.");
			throw new InvalidSSLException();
		}

		return true;
	}
}