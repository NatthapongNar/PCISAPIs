<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('memory_limit', '256M'); 
ini_set('sqlsrv.ClientBufferMaxKBSize', '524288');
ini_set('pdo_sqlsrv.client_buffer_max_kb_size', '524288');

require APPPATH . 'libraries/ArrayToXML.php';

class Dbapi extends CI_Model {
	
	public function __construct() {
		parent::__construct();
	}
	
	// GET GRID INFORMATION
	public function getDefendTable($data) {
		$this->load->model('dbmodel');
		
		$AuthID 		= !empty($data['EmpCode']) ? "'".$data['EmpCode']."'":'NULL';		
		$AppNo			= !empty($data['AppNo']) ? "'".$data['AppNo']."'":'NULL';
		$AreaCode		= !empty($data['AreaCode']) ? "'".$data['AreaCode']."'":'NULL';
		$BrnCode		= !empty($data['BrnCode']) ? "'".$data['BrnCode']."'":'NULL';
		$CAName			= !empty($data['CAName']) ? "'".$data['CAName']."'":'NULL';
		$CustName		= !empty($data['CustName']) ? "'".$data['CustName']."'":'NULL';
		$DefendDepart   = !empty($data['DefendDepart']) ? "'".$data['DefendDepart']."'":'NULL';
		$DefendProgress	= !empty($data['DefendProgress']) ? "'".$data['DefendProgress']."'":'NULL';
		$EDF_Date		= !empty($data['EDF_Date']) ? "'".$data['EDF_Date']."'":'NULL';
		$SDF_Date		= !empty($data['SDF_Date']) ? "'".$data['SDF_Date']."'":'NULL';
		$IDCard			= !empty($data['DCard']) ? "'".$data['DCard']."'":'NULL';
		$RMCode			= !empty($data['RMCode']) ? "'".$data['RMCode']."'":'NULL';
		$RegionCode		= !empty($data['RegionCode']) ? "'".$data['RegionCode']."'":'NULL';
		$Status			= !empty($data['Status']) ? "'".$data['Status']."'":'NULL';		
		$ActiveRow 	    = !empty($data['ActiveRow']) ? "'".$data['ActiveRow']."'":'NULL';
		
		$set_param = "
			@EmpCode = $AuthID,
			@RegionCode = $RegionCode,
			@AreaCode = $AreaCode,
			@BrnCode= $BrnCode, 
			@RMCode = $RMCode,
			@AppNo = $AppNo,
			@Status = $Status,
			@DefendDepart = $DefendDepart,
			@DefendProcess = NULL,
			@DefendProgress = $DefendProgress,
			@CAName = $CAName,
			@CustName = $CustName,
			@IDCard = $IDCard,
			@SDF_Date = $SDF_Date,
			@EDF_Date = $EDF_Date,
			@SourceChannel = NULL,
			@ActiveRow = $ActiveRow
		";
		
		$result_set = $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_Customize] $set_param");
		
		unset($AuthID);
		unset($Isactive);
		unset($set_param);
		
		return array(
			'data'	  => $result_set['data'],
			'status'  => TRUE,
			'msg'	  => 'success'
		);
		
	}

	public function getDefendIssueHead($doc_id) {
		$this->load->model('dbmodel');		
		return $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_IssueHeadRead] @DocID = '".$doc_id."'");
	}
	
	public function getDefendIssueList($doc_id) {
		$this->load->model('dbmodel');
		return $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_IssueContentRead] @DocID = '".$doc_id."'");
	}
	
	// ADD NEW ON 04/04/2018
	public function getDefendReactivation($doc_id) {
		$this->load->model('dbmodel');
		return $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_ReactivationLog] @DocID = '".$doc_id."'");
	}
	
	public function requestDefendToManager($data) {
		$this->load->model('dbmodel');
		date_default_timezone_set("Asia/Bangkok");
		
		$doc_id		= $data['DocID'];
		$def_id		= 1;
		$cust_id 	= $data['RequestID'];
		$cust_name	= $data['RequestBy'];
		$current	= date('Y-m-d H:i:s');
		
		$log_set = array(
			'DocID'				=> $doc_id,
			'DefendRef' 		=> $def_id,
			'DefendProgress'	=> 'LB Submit',
			'AssignID'			=> $cust_id,
			'AssignBy'			=> $cust_name,
			'AssignDate'		=> $current
		);
		
		$data_set = array(
				'DefendProgress'	=> 'LB Submit',
				'AssignmentDate'	=> $current,
				'AssignmentConfirm'	=> 'N',
				'UpdateID'			=> $cust_id,
				'UpdateBy'			=> $cust_name,
				'UpdateDate'		=> $current
		);
		
		$SignDataLog = $this->dbmodel->exec('New_DefendAssignLogs', $log_set, false, 'insert');
		$UpdateData  = $this->dbmodel->exec('New_DefendHead', $data_set, array(
			'DocID'			=> $doc_id,
			'DefendRef' 	=> $def_id
		), 'update');
		
		unset($doc_id);
		unset($def_id);
		unset($cust_id);
		unset($cust_name);
		unset($current);
		unset($log_set);
				
		if($SignDataLog == TRUE && $UpdateData == TRUE) {
			
			unset($SignDataLog);
			unset($UpdateData);
			
			return array(
				'data' 		=> $data_set,
				'status'	=> true,
				'msg'		=> 'success'
			);
		} else {
			
			unset($SignDataLog);
			unset($UpdateData);
			
			return array(
				'data' 		=> array(),
				'status'	=> false,
				'msg'		=> 'failed'
			);
		}
		
	}
	
	// ADD NEW ON 04/04/2018
	public function activeItemsForGenerationPDF($data) {
		$this->load->model('dbmodel');
		date_default_timezone_set("Asia/Bangkok");
		
		if(!empty($data['DocID'])):
			return $this->dbmodel->exec('New_DefendReactivationLog', array('IsVisible' => $data['State']), array('DocID' => $data['DocID'], 'DefendCode' => $data['DefendCode']), 'update');
		else:
			return FALSE;
		endif;
	}
	
	// MODIFY ON 04/04/2018
	public function updateProcessManagerHandled($data) {
		$this->load->model('dbmodel');
		date_default_timezone_set("Asia/Bangkok");

		$DataLog	 = FALSE;
		$DataUpdate  = FALSE;
		$DecisionLog = FALSE;
		$MgrApvLog	 = FALSE;
		
		$doc_id		 = $data['DocID'];
		$def_id		 = 1;
		$def_state	 = !empty($data['DefendProgress']) ? $data['DefendProgress']:null;
		$def_score	 = !empty($data['DefendScore']) ? $data['DefendScore']:null;
		$remark		 = !empty($data['Remark']) ? $data['Remark']:null;
		$cust_id 	 = $data['RequestID'];
		$cust_name	 = $data['RequestBy'];
		$current	 = date('Y-m-d H:i:s');
		
		$log_set = array(
			'DocID'				=> $doc_id,
			'DefendRef' 		=> $def_id,
			'DefendProgress'	=> $def_state,
			'AssignID'			=> $cust_id,
			'AssignBy'			=> $cust_name,
			'AssignDate'		=> $current
		);
		
		$data_set = array(
			'DefendProgress'	=> $def_state,
			'DefendScore'		=> $def_score,
			'AssignmentDate'	=> $current,			
			'AssignmentConfirm'	=> (in_array($def_state, array('Re-Process', 'Incompleted'))) ? 'N':'Y',
			'Remark'			=> $remark,
			'UpdateID'			=> $cust_id,
			'UpdateBy'			=> $cust_name,
			'UpdateDate'		=> $current
		);
		
		if($def_state === 'Draft') {
			$data_set['DefendDate'] = $current;
		}
				
		$DataLog = $this->dbmodel->exec('New_DefendAssignLogs', $log_set, false, 'insert');
		
		if($DataLog == TRUE):
			$DataUpdate = $this->dbmodel->exec('New_DefendHead', $data_set, array('DocID'	=> $doc_id, 'DefendRef' => $def_id), 'update');
		endif;

		if($DataUpdate == TRUE):
			$this->dbmodel->exec ('New_DefendDecisionLog', array (
				'DocID' 		=> $doc_id,
				'DefendRef' 	=> $def_id,
				'EventProcess'  => $def_state,
				'Remark'		=> $remark,
				'CreateID' 		=> $cust_id,
				'CreateName'  	=> $cust_name,
				'CreateDate'    => $current
			), false, 'insert');

		endif;
			
		if(in_array($def_state, array('Send to CA', 'Re-Process', 'Incompleted'))) {			
			$this->dbmodel->exec ('New_DefendManagerAppraisal', array (
				'DocID' 		=> $doc_id,
				'DefendRef' 	=> $def_id,
				'ScoreRating'   => $def_score,
				'CreateID' 		=> $cust_id,
				'CreateBy'  	=> $cust_name,
				'CreateDate'    => $current
			), false, 'insert');
			
			if(in_array($def_state, array('Send to CA'))) {
				/*
				$results = $this->dbmodel->loadData('ApplicationStatus', "ApplicationNo", array('DocID' => $doc_id));
				if(!empty($results['data'][0]['ApplicationNo'])):
					$this->dbmodel->CIQuery("[dbo].[sp_pcis_defend_INSERT_ToSDE] @ApplicationNo = '".$results['data'][0]['ApplicationNo']."', @EmpID = '".$cust_id."', @SendBy =  '".$cust_name."'");
				endif;
				*/				

				$this->adjustmentContentIntoSystemLog($doc_id, array('EmpID' => $cust_id, 'EmpName' => $cust_name));
			}
			
		}
		
		unset($doc_id);
		unset($def_id);
		unset($def_state);
		unset($remark);
		unset($cust_id);
		unset($cust_name);
		unset($current);
		unset($log_set);		
			
		if($DataLog == TRUE && $DataUpdate == TRUE) {
			
			unset($DataLog);
			unset($DataUpdate);
	
			return array(
				'data' 		=> $data_set,
				'status'	=> true,
				'msg'		=> 'success'
			);
			
			
		} else {
			
			unset($DataLog);
			unset($DataUpdate);
			
			return array(
				'data' 		=> array(),
				'status'	=> false,
				'msg'		=> 'failed'
			);
		}
	
	}
	
	public function adjustmentContentIntoSystemLog($doc_id, $user) {
		$this->load->model('dbmodel');
		date_default_timezone_set("Asia/Bangkok");

		$result = $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_IssueContentRead] @DocID = '".$doc_id."'");	
		
		self::onDefendFileGenerationBackup($doc_id, $result, $user);
		
		if(!empty($result['data']) && count($result['data']) > 0) {

			$result_log = $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_AllowContentLogs] @DocID = '".$doc_id."'");
			
			$state = array();
			foreach ($result['data'] as $index => $value) {
				$document_id = $value['DocID'];
				$defend_code = $value['DefendCode'];
			
				$logs_state = $this->dbmodel->data_validation('New_DefendAllowContent', '*', array('DocID' => $document_id, 'DefendCode' => $defend_code), false);
				if($logs_state === TRUE) {
					$logs = self::findData($result_log['data'], 'DefendCode', $value['DefendCode']);
					
					$defend_note_1 	= !empty($value['DefendNote1']) ? $value['DefendNote1'] : '';
					$defend_note_2 	= !empty($value['DefendNote2']) ? $value['DefendNote2'] : '';
					$defend_note_3 	= !empty($value['DefendNote3']) ? $value['DefendNote3'] : '';
					$defend_note_4 	= !empty($value['DefendNote4']) ? $value['DefendNote4'] : '';
					$defend_note_5 	= !empty($value['DefendNote5']) ? $value['DefendNote5'] : '';
					$defend_note_6 	= !empty($value['DefendNote6']) ? $value['DefendNote6'] : '';
					$defend_note_7 	= !empty($value['DefendNote7']) ? $value['DefendNote7'] : '';
					$defend_note_8 	= !empty($value['DefendNote8']) ? $value['DefendNote8'] : '';
					$defend_note_9 	= !empty($value['DefendNote9']) ? $value['DefendNote9'] : '';
					$defend_note_10 = !empty($value['DefendNote10']) ? $value['DefendNote10'] : '';
					
					$defend_logs = $logs['DefendNote'];
					$defend_note = (
						$defend_note_1 .
						$defend_note_2 .
						$defend_note_3 .
						$defend_note_4 .
						$defend_note_5 .
						$defend_note_6 .
						$defend_note_7 .
						$defend_note_8 .
						$defend_note_9 .
						$defend_note_10
					);
					
					$check_logs  = !empty($defend_logs) ? $defend_logs:'';
					
					$note_concat = $defend_note . $check_logs;
					$note_splits = self::str_split_unicode($note_concat, 4000);
					
					$objData = array(	
						'DocID' 			=> $value['DocID'],
						'DefendRef' 		=> $value['DefendRef'],
						'DefendCode' 		=> $value['DefendCode'],
						'UpdateID' 			=> $user['EmpID'],
						'UpdateName' 		=> $user['EmpName'],
						'UpdateDate'		=> date('Y-m-d H:i:s')
					);
					
					if(!empty($note_splits) && count($note_splits) > 0) {
						$index = 1;
						foreach ($note_splits as $i => $v) {
							$key = 'DefendNote' . $index;
							$objData[$key]	= $v;
							$index++;
						}
					} else {
						$objData['DefendNote1']	= NULL;
					}
					
					array_push($state, $this->dbmodel->exec('New_DefendAllowContent', $objData, array('DocID' => $value['DocID'], 'DefendCode' => $value['DefendCode']), 'update'));
									
					unset($logs);					
					unset($defend_note_1);
					unset($defend_note_2);
					unset($defend_note_3);
					unset($defend_note_4);
					unset($defend_note_5);
					unset($defend_note_6);
					unset($defend_note_7);
					unset($defend_note_8);
					unset($defend_note_9);
					unset($defend_note_10);						
					unset($defend_note);
					unset($defend_logs);
					
				} else {
					
					array_push($state, $this->dbmodel->exec('New_DefendAllowContent',
						array(
								'DocID' 			=> $value['DocID'],
								'DefendRef' 		=> $value['DefendRef'],
								'DefendCode' 		=> $value['DefendCode'],
								'DefendTitleOption'	=> !empty($value['DefendTitleOption']) ? $value['DefendTitleOption']: NULL,
								'DefendNote1' 		=> !empty($value['DefendNote1']) ? $value['DefendNote1'] : NULL,
								'DefendNote2' 		=> !empty($value['DefendNote2']) ? $value['DefendNote2'] : NULL,
								'DefendNote3' 		=> !empty($value['DefendNote3']) ? $value['DefendNote3'] : NULL,
								'DefendNote4' 		=> !empty($value['DefendNote4']) ? $value['DefendNote4'] : NULL,
								'DefendNote5' 		=> !empty($value['DefendNote5']) ? $value['DefendNote5'] : NULL,
								'DefendNote6' 		=> !empty($value['DefendNote6']) ? $value['DefendNote6'] : NULL,
								'DefendNote7' 		=> !empty($value['DefendNote7']) ? $value['DefendNote7'] : NULL,
								'DefendNote8' 		=> !empty($value['DefendNote8']) ? $value['DefendNote8'] : NULL,
								'DefendNote9' 		=> !empty($value['DefendNote9']) ? $value['DefendNote9'] : NULL,
								'DefendNote10'  	=> !empty($value['DefendNote10']) ? $value['DefendNote10'] : NULL,
								'IsActive' 			=> 'A',
								'CreateID' 			=> $user['EmpID'],
								'CreateName' 		=> $user['EmpName'],
								'CreateDate'		=> date('Y-m-d H:i:s')
						), false, 'insert')
					);
					
				}
			
				unset($document_id);
				unset($defend_code);				
				unset($logs_state);				
			}
			
			if(!in_array(FALSE, $state)) {
				foreach ($result['data'] as $index => $value) {
					$this->dbmodel->exec('New_DefendSubHead',
						array(
							'DocID' 			=> $value['DocID'],
							'DefendRef' 		=> $value['DefendRef'],
							'DefendCode' 		=> $value['DefendCode'],
							'DefendTitleOption'	=> !empty($value['DefendTitleOption']) ? $value['DefendTitleOption'] : NULL,
							'DefendNote1' 		=> NULL,
							'DefendNote2' 		=> NULL,
							'DefendNote3' 		=> NULL,
							'DefendNote4' 		=> NULL,
							'DefendNote5' 		=> NULL,
							'DefendNote6' 		=> NULL,
							'DefendNote7' 		=> NULL,
							'DefendNote8' 		=> NULL,
							'DefendNote9' 		=> NULL,
							'DefendNote10'  	=> NULL,
							'IsActive' 			=> 'A',
							'CreateID' 			=> $user['EmpID'],
							'CreateName' 		=> $user['EmpName'],
							'CreateDate'		=> date('Y-m-d H:i:s')
						),
						array(
							'DocID' 			=> $value['DocID'],
							'DefendCode' 		=> $value['DefendCode'],
						),
						'update'
					);
				}
				
				unset($state);
				unset($result);
				
				return TRUE;
				
			} else {
				return FALSE;
			}
						
		} else {
			return FALSE;
		}
				
	}
	
	static function str_split_unicode($str, $l = 0) {
		if ($l > 0) {
			$ret = array();
			$len = mb_strlen($str, "UTF-8");
			for ($i = 0; $i < $len; $i += $l) {
				$ret[] = mb_substr($str, $i, $l, "UTF-8");
			}
			return $ret;
		}
		return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	static function onDefendFileGenerationBackup($doc_id, $result, $user, $filename = '', $new_path = '') {
		date_default_timezone_set("Asia/Bangkok");			
		$local_pathname = 'E:\PCIS_Form_Backup\\' . $new_path;	
		
		if(!is_dir($local_pathname)) {
			mkdir($local_pathname, 0777, true);
		}

		if(!empty($doc_id)):					
			$xml = new ArrayToXML();
			$filename = !empty($filename) ? $filename : 'DEFEND_' . $doc_id . '_' . date('YmdHi') . '.xml';
			$specific_local_path = $local_pathname . $filename;
			
			file_put_contents(
				$specific_local_path, 
				$xml->buildXML(
					array(
						'Data' 		=> $result['data'], 
						'Requestor'	=> array(
							'UserID'	=> $user['EmpID'],
							'UserName'	=> $user['EmpName'],
							'OnDate'	=> date('Y-m-d H:i:s')
						)
					),
					'Defend'
				)
			);	

		endif;
	}
	
	static function getParentStackComplete($child, $stack) {
		$return = array();
		foreach ($stack as $k => $v) {
			if (is_array($v)) {
				$stack = self::getParentStackComplete($child, $v);
				
				if (is_array($stack) && !empty($stack)) {
					$return[$k] = $stack;
				}
			} else {
				if ($v == $child) {
					$return[$k] = $child;
				}
			}
		}
		return empty($return) ? false: $return;
	}
	
	static function findData($array, $key, $value){
		$holding = null;
		if(!empty($array) && count($array) > 0) {
			foreach($array as $k => $v){
				if($v[$key] == $value){
					$holding = $array[$k];
				}
			}
		}		
		return $holding;
	}
	
	public function addNewTopicData($data) {
		$this->load->model ('dbmodel');
		date_default_timezone_set ( "Asia/Bangkok" );
		
		$doc_id = $data ['DocID'];
		$def_id = 1;
		$topic_item = $data ['TopicList'];
		$cust_id = $data ['CreateID'];
		$cust_name = $data ['CreateName'];
		$current = date ( 'Y-m-d H:i:s' );
		
		if (!empty($topic_item[0])) {
			
			foreach ( $topic_item as $index => $value ) {
				$data_set = array (
					'DocID' => $doc_id,
					'DefendCode' => $value,
					'DefendRef'  => 1,
					'IsActive'   => 'A',
					'CreateID'   => $cust_id,
					'CreateName' => $cust_name,
					'CreateDate' => $current 
				);
				
				$this->dbmodel->exec('New_DefendSubHead', $data_set, false, 'insert' );
			}
			
			$log_set = array (
				'DocID' => $doc_id,
				'ApplicationNo' => NULL,
				'EventProcess' => 'DEFEND CREATE TOPIC',
				'CreateByID' => $cust_id,
				'CreateByName' => $cust_name,
				'CreateByDate' => $current 
			);
			
			$this->dbmodel->exec('PCISEventLogs', $log_set, false, 'insert' );
			
			unset($def_id);
			unset($cust_id);
			unset($cust_name);
			unset($current);	
			
			return array (
				'data'	 => $topic_item,
				'status' => true,
				'msg' 	 => 'success' 
			);
			
		}	
	}
	
	public function deleteTopicData($data) {
		$this->load->model ('dbmodel');
		date_default_timezone_set ( "Asia/Bangkok" );
		
		$doc_id 	= $data ['DocID'];
		$def_id 	= $data ['DefendCode'];
		$cust_id 	= $data ['CreateByID'];
		$cust_name  = $data ['CreateByName'];
		$current 	= date ( 'Y-m-d H:i:s' );
		
		$this->dbmodel->exec('New_DefendSubHead', array('IsActive' => 'N'), array('DocID' => $doc_id, 'DefendCode' => $def_id), 'update');
		
		$log_set = array (
			'DocID' 		=> $doc_id,
			'ApplicationNo' => NULL,
			'EventProcess' 	=> 'DEFEND DELETE TOPIC [' . $def_id . ']',
			'CreateByID' 	=> $cust_id,
			'CreateByName' 	=> $cust_name,
			'CreateByDate'	=> $current
		);
		
		$this->dbmodel->exec ('PCISEventLogs', $log_set, false, 'insert' );
		
		unset($doc_id);
		unset($def_id);
		unset($cust_id);
		unset($cust_name);
		unset($current);
		unset($log_set);
		
		return array (
			'data'	 => array('DocID' => $data ['DocID'], 'DefendCode' => $data ['DefendCode']),
			'status' => true,
			'msg' 	 => 'success'
		);
		
	}
	
	// ADD NEW ON 04/04/2018
	public function reactivationHandled($data) {
		$this->load->model ('dbmodel');
		date_default_timezone_set ( "Asia/Bangkok" );
		
		$doc_id 	= !empty($data['DocID']) ? $data['DocID'] : null;		
		$cust_id 	= $data ['RequestID'];
		$cust_name  = $data ['RequestBy'];
		
		$current_data = array();
		$result = $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_IssueContentRead] @DocID = '".$doc_id."'");
					
		if(!empty($result['data']) && count($result['data']) > 0) {
	
			foreach ($result['data'] as $index => $value) {
				
				$this->addTopicForBackup($value);

				if(empty($value['DefendNote'])): continue;
				else:
					array_push($current_data, 
						array(
							'DocID' 			=> $value['DocID'],
							'DefendRef' 		=> $value['DefendRef'],
							'DefendCode' 		=> $value['DefendCode'],
							'DefendState'		=> 'RM ON HAND',
							'DefendNote1' 		=> !empty($value['DefendNote1']) ? $value['DefendNote1'] : NULL,
							'DefendNote2' 		=> !empty($value['DefendNote2']) ? $value['DefendNote2'] : NULL,
							'DefendNote3' 		=> !empty($value['DefendNote3']) ? $value['DefendNote3'] : NULL,
							'DefendNote4' 		=> !empty($value['DefendNote4']) ? $value['DefendNote4'] : NULL,
							'DefendNote5' 		=> !empty($value['DefendNote5']) ? $value['DefendNote5'] : NULL,
							'DefendNote6' 		=> !empty($value['DefendNote6']) ? $value['DefendNote6'] : NULL,
							'DefendNote7' 		=> !empty($value['DefendNote7']) ? $value['DefendNote7'] : NULL,
							'DefendNote8' 		=> !empty($value['DefendNote8']) ? $value['DefendNote8'] : NULL,
							'DefendNote9' 		=> !empty($value['DefendNote9']) ? $value['DefendNote9'] : NULL,
							'DefendNote10'  	=> !empty($value['DefendNote10']) ? $value['DefendNote10'] : NULL,
							'IsVisible'			=> 'N',
							'IsActive' 			=> 'A',
							'CreateID' 			=> $cust_id,
							'CreateName' 		=> $cust_name,
							'CreateDate'		=> date('Y-m-d H:i:s')
						)
					);		
				endif;
			}
	
		}
	
		$result_log = $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_AllowContentLogs] @DocID = '".$doc_id."'");
		if(!empty($result_log['data']) && count($result_log['data']) > 0) {
			foreach ($result_log['data'] as $index => $value) {
				
				$defend_note = !empty($value['DefendNote']) ? $value['DefendNote']:'';				
				if(empty($defend_note)) { continue; }
				else {
					
					$defend_logs = array(
						'DocID' 			=> $value['DocID'],
						'DefendRef' 		=> $value['DefendRef'],
						'DefendCode' 		=> $value['DefendCode'],
						'DefendState'		=> 'SENT TO CA',
						'IsVisible'			=> 'N',
						'IsActive' 			=> 'A',
						'CreateID' 			=> $cust_id,
						'CreateName' 		=> $cust_name,
						'CreateDate'		=> date('Y-m-d H:i:s')
					);
					
					$note_splits = self::str_split_unicode($defend_note, 4000);
					
					if(!empty($note_splits) && count($note_splits) > 0) {
						$index = 1;
						foreach ($note_splits as $i => $v) {
							$key = 'DefendNote' . $index;
							$defend_logs[$key]	= $v;
							$index++;
						}
					} else {
						$defend_logs['DefendNote1']	= NULL;
					}
					
					if(!empty($defend_logs['DefendNote1'])) {
						array_push($current_data, $defend_logs);
					}				
					
				}
				
			}
			
		}
		
		if(count($current_data) > 0) {
			$state = array();
			foreach ($current_data as $i => $v) {
				array_push($state, $this->dbmodel->exec ('New_DefendReactivationLog', $v, false, 'insert'));
				$index++;	
			}
			
			if(!in_array(FALSE, $state)) {				
				self::onDefendFileGenerationBackup($doc_id, array('data' => $current_data), array('EmpID' => $cust_id, 'EmpName' => $cust_name), 'REACTIVATION_' . $doc_id . '_' . date('YmdHi') . '.xml', 'PCIS_REACTIVATED\\');
			
				if(!empty($doc_id)):
					$this->dbmodel->deleteItems('New_DefendSubHead', array('DocID' => $doc_id));
					$this->dbmodel->deleteItems('New_DefendAllowContent', array('DocID' => $doc_id));
				endif;				
				
				$defend_items = array('SP001', 'SP002', 'SP003', 'SP004');
				foreach ($defend_items as $i => $v) {
					$this->dbmodel->exec('New_DefendSubHead',
						array(
							'DocID' 			=> $doc_id,
							'DefendRef' 		=> '1',
							'DefendCode' 		=> $v,
							'DefendTitleOption'	=> NULL,
							'DefendNote1' 		=> NULL,
							'DefendNote2' 		=> NULL,
							'DefendNote3' 		=> NULL,
							'DefendNote4' 		=> NULL,
							'DefendNote5' 		=> NULL,
							'DefendNote6' 		=> NULL,
							'DefendNote7' 		=> NULL,
							'DefendNote8' 		=> NULL,
							'DefendNote9' 		=> NULL,
							'DefendNote10'  	=> NULL,
							'IsActive' 			=> 'A',
							'CreateID' 			=> $cust_id,
							'CreateName' 		=> $cust_name,
							'CreateDate'		=> date('Y-m-d H:i:s')
						), false,
						'insert'
					);
				}
				
				// CHANGE STATUS
				$this->updateProcessManagerHandled($data);
				
				unset($doc_id);
				unset($cust_id);
				unset($cust_name);
				unset($result);
				unset($result_log);
				unset($state);
				unset($defend_items);
				unset($current_data);
				
				return TRUE;
				
			} else {
				return FALSE;
			}
			
		} else {
			
			$this->dbmodel->exec('New_DefendHead', 
				array(
					'DefendProgress' => 'Draft',
					'UpdateID' 		 => $cust_id,
					'UpdateBy'	 	 => $cust_name,
					'UpdateDate' 	 => date('Y-m-d H:i:s')
				), 
				array('DocID' => $doc_id), 
				'update'
			);
			
			$this->dbmodel->exec('New_DefendAssignLogs', array(
					'DocID' 			=> $doc_id,
					'DefendRef' 		=> 1,
					'DefendProgress'	=> 'Draft',
					'AssignDate'		=> date('Y-m-d H:i:s'),
					'AssignID' 			=> $cust_id,
					'AssignBy' 			=> $cust_name
			), false, 'insert');
			
			return FALSE;
			
		}
						
	}
	
	// ADD NEW ON 04/04/2018
	public function addTopicForBackup($value) {
		$this->load->model('dbmodel');
		date_default_timezone_set ( "Asia/Bangkok" );
		
		$data = array(
			'DocID' 			=> $value['DocID'],
			'DefendRef' 		=> $value['DefendRef'],
			'DefendCode' 		=> $value['DefendCode'],
			'DefendTitleOption'	=> !empty($value['DefendTitleOption']) ? $value['DefendTitleOption'] : NULL,
			'DefendNote1' 		=> !empty($value['DefendNote1']) ? $value['DefendNote1'] : NULL,
			'DefendNote2' 		=> !empty($value['DefendNote2']) ? $value['DefendNote2'] : NULL,
			'DefendNote3' 		=> !empty($value['DefendNote3']) ? $value['DefendNote3'] : NULL,
			'DefendNote4' 		=> !empty($value['DefendNote4']) ? $value['DefendNote4'] : NULL,
			'DefendNote5' 		=> !empty($value['DefendNote5']) ? $value['DefendNote5'] : NULL,
			'DefendNote6' 		=> !empty($value['DefendNote6']) ? $value['DefendNote6'] : NULL,
			'DefendNote7' 		=> !empty($value['DefendNote7']) ? $value['DefendNote7'] : NULL,
			'DefendNote8' 		=> !empty($value['DefendNote8']) ? $value['DefendNote8'] : NULL,
			'DefendNote9' 		=> !empty($value['DefendNote9']) ? $value['DefendNote9'] : NULL,
			'DefendNote10'  	=> !empty($value['DefendNote10']) ? $value['DefendNote10'] : NULL,
			'IsActive' 			=> $value['IsActive'],
			'CreateID' 			=> $value['CreateID'],
			'CreateName' 		=> $value['CreateName'],
			'CreateDate'		=> $value['CreateDate']
		);
		
		$this->dbmodel->exec('New_DefendSubHeadLog', $data, false, 'insert');
		unset($data);
	}
	
	// ADD NEW ON 20/05/2018
	public function createDefendForum($value) {
		$this->load->model('dbmodel');
		date_default_timezone_set ( "Asia/Bangkok" );
		
		$DocID   = !empty($value['DocID']) ? "'".$value['DocID']."'":NULL;
		$AppNo   = !empty($value['ApplicationNo']) ? "'".$value['ApplicationNo']."'":NULL;
		$D_Note  = !empty($value['DefendNote']) ? "'".$value['DefendNote']."'": NULL;
		$E_Code  = !empty($value['RequestID']) ? "'".$value['RequestID']."'":NULL;
		$E_Name  = !empty($value['RequestBy']) ? "'".$value['RequestBy']."'":NULL;
		
		$data = "
		@ForumDocID = $DocID,
		@ForumApplicationNo = $AppNo,
		@ForumEmpID = $E_Code,
		@ForumEmpName = $E_Name,
		@ForumIssueReason =  $D_Note
		";
		
		$this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboardForum_Save] $data");
		unset($data);
		
	}
	
	public function deleteDefendForum($value) {
		$this->load->model('dbmodel');
		date_default_timezone_set ( "Asia/Bangkok" );
		
		$DocID   = !empty($value['DocID']) ? "'".$value['DocID']."'":NULL;
		
		$this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboardForum_Delete] @ForumDocID = $DocID");
		unset($data);
		
	}
	
	public function loadDefendTopicReason() {
		$this->load->model('dbmodel');
		return $this->dbmodel->CIQuery("SELECT * FROM [dbo].[v_DefendTopicReasonData]");
	}
	
	public function loadDefendTopicReasonLogs($data) {
		$this->load->model('dbmodel');
		return $this->dbmodel->CIQuery("[dbo].[sp_PCIS_DefendDashboard_IssueContentLogs] @DocID = '".$data."'");
	}
		
	public function loadActionNote($data) {
		$this->load->model('dbmodel');
		return $this->dbmodel->CIQuery("[dbo].[PCIS_GetActionNoteLogs] @DocID = '".$data."'");
	}
	
	public function loadMissingDoc($data) {
		$this->load->model('dbmodel');
		return $this->dbmodel->CIQuery("[dbo].[sp_MissingDoc_READ] @DocON = '".$data."'");
	}
	
	
}