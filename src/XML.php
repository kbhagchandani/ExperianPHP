<?php

namespace Experian;

class XML {
	public static function encode($mixed, $domElement=null, $DOMDocument=null,$formatedOutput=false) {
		if (is_null($DOMDocument)) {
			$DOMDocument =new \DOMDocument;
			$DOMDocument->formatOutput = true;
			self::encode($mixed, $DOMDocument, $DOMDocument,$formatedOutput);
			$DOMDocument->formatOutput = $formatedOutput;
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

					self::encode($mixedElement, $node, $DOMDocument,$formatedOutput);
				}
			}
			else {
				$mixed = is_bool($mixed) ? ($mixed ? 'true' : 'false') : $mixed;
				$domElement->appendChild($DOMDocument->createTextNode($mixed));
			}
		}
	}

	public static function decode($xmlStr){
		$doc = new \DOMDocument();
		$doc->loadXML($xmlStr);
		$nodes = $doc->documentElement;
		return self::dom2array($nodes);
	}

	private static function dom2array($dom,$data=[]){
		foreach ($dom->childNodes AS $node) {
			if($node->childNodes->length && XML_TEXT_NODE !== $node->firstChild->nodeType && $node->hasChildNodes()) {
				$data[$node->nodeName]=self::dom2array($node);
			} else if(isset($data[$node->nodeName])) {
				if(!is_array($data[$node->nodeName])){
					$previousNodeValue=$data[$node->nodeName];
					$data[$node->nodeName]=[
						$previousNodeValue,
						$node->nodeValue
					];
				} else {
					$data[$node->nodeName][]=$node->nodeValue;
				}
			} else {
				$data[$node->nodeName]=$node->nodeValue;
			}
		}
		return $data;
	}
}