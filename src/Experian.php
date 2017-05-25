<?php
namespace Experian;

use Experian\Request;
use Experian\AddOns;
use Experian\PreQualificationReport;

class Experian {

	private $config;
	public $request;

	private $data=[];
	public $addOns;

	public function __construct($config){
		$this->config=$config;
		$this->addOns=new AddOns();
		$this->request=new Request($config);
	}

	public function set($key,$value){
		$this->data[$key]=$value;
	}

	public function getPreQualificationReport(){
		$products=PreQualificationReport::prepareRequestData($this);
		$response=$this->request->getARFResponse($products);
		return PreQualificationReport::extractReport($response);
	}

	public function getUserData(){
		return $this->data;
	}
	
}
