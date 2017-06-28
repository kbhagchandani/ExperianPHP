<?php
namespace Experian;

use Experian\Request;
use Experian\AddOns;
use Experian\PreQualificationReport;
use Experian\Validation;
use Experian\Exceptions\{MissingKeyFileException, InvalidKeyFileException, KeyFileWriteException, PasswordUpdateException};
use Experian\Exceptions\{InvalidAuthException, UnauthorizedException, AccountBlockedException};

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Experian {

	private $config;
	public $request;

	private $data=[];
	public $addOns;
	private $loadedSystemConfig;

	public function __construct($config){
		self::validateConfig($config);
		$this->readConfig($config);
		$this->loadedSystemConfig=$config;
		$this->addOns=new AddOns();
		$this->log = new Logger('ExperianRequests');
		$this->log->pushHandler(new StreamHandler($this->loadedSystemConfig["logFile"], Logger::DEBUG));
		$this->request=new Request($this->config,$this->loadedSystemConfig,$this->log);
	}

	public function isAccessAllowed() {
		if($this->config['failue_count'] ?? false){
			if($this->config['failue_count']>=2)
				throw new AccountBlockedException();
		}
		if(intVal(floor((time()-$this->loadedSystemConfig['lastTimePasswordUpdated'])/86400))>30){
			if($this->loadedSystemConfig['autoPasswordReset'] ?? false){
				$this->resetPassword();
			} else {
				throw new PasswordUpdateException();
			}
		}
		return true;
	}

	public function set($key,$value){
		$this->data[$key]=$value;
	}

	public function getPreQualificationReport(){
		try {
			$products=PreQualificationReport::prepareRequestData($this);
			$response=$this->request->getARFResponse($products);
			return PreQualificationReport::extractReport($response);
		} catch (UnauthorizedException | InvalidAuthException $e) {
			$this->handleAuthFailure($e);
		}
	}

	public function getUserData(){
		return $this->data;
	}

	private function readConfig($config){
		if(!file_exists($config['keyFile']))
			throw new MissingKeyFileException();
			
		$rawCredentials=file_get_contents($config['keyFile']);
		$key = substr(sha1($config['key'], true), 0, 16);
		$rawCredentials=openssl_decrypt($rawCredentials,'DES3', $key, null, substr($config['iv'],0,8));
		$this->config=json_decode($rawCredentials,true);
		if(!$this->config)
			throw new InvalidKeyFileException();
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
			throw new KeyFileWriteException();
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
		$rawData=openssl_encrypt(json_encode($inputConfig), 'DES3', $key, null, substr($config['iv'],0,8));
		file_put_contents($config['keyFile'], $rawData);
	}

	private function resetPassword(){
		$newPassword=$this->request->resetPassord();
		$this->updatePassword($newPassword);
		$this->loadedSystemConfig['lastTimePasswordUpdated']=time();
		call_user_func($this->loadedSystemConfig['updateExperianConfig']);
	}

	private function handleAuthFailure(&$e){
		$failureCount=$this->config['failue_count'] ?? 0;
		$failureCount++;
		$this->updateFailureCount($failureCount);
		if($failureCount>=3){
			$this->log->emergency("Permanent Auth Failure. Account may be locked.");
			throw new AccountBlockedException();
		} else {
			$this->log->alert("Auth Failed for $failureCount time.");
			throw $e;
		}
	}
/**
 *	This Function is designed to increase the failure count
 *	and is useful for test purpose only
 *	@param $count [int]
 */
	public function updateFailureCount($count){
		$this->config['failue_count']=$count;
		$this->saveConfig();
	}

	public function updatePassword($newPassword){
		$this->config['password']=$newPassword;
		unset($this->config['failue_count']);
		$this->saveConfig();
		call_user_func($this->loadedSystemConfig['updateExperianConfig']);
	}

	private function saveConfig(){
		$config=$this->loadedSystemConfig;
		if(!is_writable($config['keyFile']))
			throw new KeyFileWriteException();
		$key = substr(sha1($config['key'], true), 0, 16);
		$rawData=openssl_encrypt(json_encode($this->config), 'DES3', $key, null, substr($config['iv'],0,8));
		file_put_contents($config['keyFile'], $rawData);
	}

	public static function getManualPasswordLink(){
		return "https://ss6.experian.com/securecontrol/logon.html";
	}
}
