<?php
namespace Experian;

use Experian\Request;
use Experian\AddOns;
use Experian\PreQualificationReport;
use Experian\Validation;
use Experian\Exceptions\{MissingKeyFile,InvalidKeyFile,KeyFileWriteError};

class Experian {

	private $config;
	public $request;

	private $data=[];
	public $addOns;

	public function __construct($config){
		self::validateConfig($config);
		$this->readConfig($config);
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

	private function readConfig($config){
		if(!file_exists($config['keyFile']))
			throw new MissingKeyFile();
			
		$rawCredentials=file_get_contents($config['keyFile']);
		$key = substr(sha1($config['key'], true), 0, 16);
		$rawCredentials=openssl_decrypt($rawCredentials,'DES3', $key, null, $config['iv']);
		$this->config=json_decode($rawCredentials,true);
		if(!$this->config)
			throw new InvalidKeyFile();
	}

	private static function validateConfig($config){
		$configSchema=[
					'keyFile'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>10
					],
					'key'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>8,
						'MaxLength'=>16
					],
					'iv'=>[
						'Required'=>false,
						'Type'=>'string',
						'MinLength'=>16
					]
				];
		Validation::validate($configSchema,$config);
	}
	
	public static function generateKeyFile($config,$inputConfig){
		self::validateConfig($config);
		if(!is_writable($config['keyFile']))
			throw new KeyFileWriteError();
		$configSchema=[
			'username'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>7
					],
			'password'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>8
					],
			'eai'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>10
					],
			'db_host'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>3
					],			// 'STAR' => for demo test, 'CIS' => for production
			'service_name'=>[
						'Required'=>true,
						'Type'=>'string',
						'MinLength'=>5
					],
			'subscriber'=>[
				'Required'=>true,
				'ChildNodes'=>[
					'preamble'=>[
							'Required'=>true,
							'Type'=>'string',
							'MinLength'=>3
						],
					'op_initials'=>[
							'Required'=>true,
							'Type'=>'string',
							'MinLength'=>2
						],
					'sub_code'=>[
							'Required'=>true,
							'Type'=>'string',
							'MinLength'=>7
						]
				]
			],
			'vendor'=>[
				'Required'=>true,
				'ChildNodes'=>[
					'vendor_number'=>[
							'Required'=>true,
							'Type'=>'string',
							'MinLength'=>3
						],
					'vendor_version'=>[
							'Required'=>true,
							'Type'=>'string',
							'MinLength'=>2
						]
				]
			]
		];
		Validation::validate($configSchema,$inputConfig);
		$key = substr(sha1($config['key'], true), 0, 16);
		$rawData=openssl_encrypt(json_encode($inputConfig), 'DES3', $key, null, $config['iv']);
		file_put_contents($config['keyFile'], $rawData);
	}
}
