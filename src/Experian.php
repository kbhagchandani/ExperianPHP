<?php
namespace Experian;

use Experian\Request;

use Experian\PreQualificationReport;

class Experian {

	private $config;
	private $request;

	private $data=[];

	private $addOns=null;

	public function __construct($config){
		$this->config=$config;
		$this->request=new Request($config);
	}

	public function set($key,$value){
		$this->data[$key]=$value;
	}

	public function getPreQualificationReport(){
		$products=PreQualificationReport::prepareRequestData($this->getARFRequest());
		$response=$this->request->getARFResponse($products);
		return PreQualificationReport::extractReport($response);
	}

	public function getARFRequest(){
		$requestData=$this->data;
		$requestData['Vendor']=$this->config['vendor'];
		$requestData['Subscriber']=$this->config['subscriber'];
		if(is_array($this->addOns)){
			$requestData['AddOns']=[];
			foreach($this->addOns as $addOn => $value){
				$requestData['AddOns'][$addOn]=$value;
			}
		}
		return $requestData;
	}
}
