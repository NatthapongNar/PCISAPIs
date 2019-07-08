<?php defined('BASEPATH') OR exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, DELETE");
header('Content-type: application/json; charset="UTF-8"');

require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function defendforum_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['DocID'] !== ''):
			$result = $this->dbapi->createDefendForum($dataset);
			$this->response(array('status' => true, 'code' => 200, 'msg' => 'success.'), 200);
			
			unset($json_params);
			unset($dataset);
			unset($result);
			exit;
			
			else:
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Authenication denied.'), 400);
			
			unset($json_params);
			unset($dataset);
			exit;
			
			endif;
			
		} else {
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Not found data.'), 400);
			
			unset($json_params);
			exit;
		}
		
	}
	
	public function defendforumDelete_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if(!empty($dataset['DocID']) && $dataset['DocID'] !== ''):
			$result = $this->dbapi->deleteDefendForum($dataset);
			$this->response(array('status' => true, 'code' => 200, 'msg' => 'success.'), 200);
			
			unset($json_params);
			unset($dataset);
			unset($result);
			exit;
			
			else:
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Authenication denied.'), 400);
			
			unset($json_params);
			unset($dataset);
			exit;
			
			endif;
			
		} else {
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Not found data.'), 400);
			
			unset($json_params);
			exit;
		}
	}
	
	public function defend_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {			
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['EmpCode'] !== ''):
				$result = $this->dbapi->getDefendTable($dataset);
				$this->response(array('data' => $result['data'], 'status' => true, 'code' => 200, 'msg' => 'success.'), 200);
				
				unset($json_params);
				unset($dataset);
				unset($result);				
				exit;
		
			else:
				$this->response(array('data' => [], 'status' => false, 'code' => 400, 'msg' => 'Authenication denied.'), 400);
				
				unset($json_params);
				unset($dataset);				
				exit;
				
			endif;
		
		} else {			
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Not found data.'), 400);
			
			unset($json_params);
			exit;
		}
		
	}
		
	// Modify On 04/04/2018
	public function defend_issue_get() {
		$this->load->model('dbapi');
		
		$doc_id = $this->get('xid');

		if(empty($doc_id)):
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'No document id specified'), 400);
			
			unset($doc_id);
			exit;
			
		else:
			
			$defend_head  = $this->dbapi->getDefendIssueHead($doc_id);
			$defend_list  = $this->dbapi->getDefendIssueList($doc_id);
			$defend_logs  = $this->dbapi->loadDefendTopicReasonLogs($doc_id);
			$defend_item  = $this->dbapi->loadDefendTopicReason();
			$defend_react = $this->dbapi->getDefendReactivation($doc_id);
	
			$param = array(
				'DefendHeader' => $defend_head['data'],
				'DefendLists'  => $defend_list['data'],
				'DefendReason' => $defend_item['data'],
				'DefendLogs'   => $defend_logs['data'],
				'DefendReactive' => $defend_react['data'],
			);
			
			$this->response($param, 200);
			
			unset($doc_id);
			unset($defend_head);
			unset($defend_list);
			unset($defend_logs);
			unset($defend_item);
			unset($param);			
			exit;
			
		endif;	
		
	}
	
	public function defendSubmitToManager_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['DocID'] !== ''):
				$result = $this->dbapi->requestDefendToManager($dataset);
				$this->response($result['data'], 200);
				
				unset($result);
				unset($json_params);
				unset($dataset);				
				exit;
				
			else:
				$this->response('Authenication denied', 400);
			
				unset($json_params);
				unset($dataset);				
				exit;
				
			endif;

		} else {
			$this->response('Not found data', 400);
			unset($json_params);
			exit;
		}
		
	}
	
	public function updateDefendProgress_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['DocID'] !== ''):
				$result = $this->dbapi->updateProcessManagerHandled($dataset);
				$this->response($result['data'], 200);
				
				unset($result);
				unset($json_params);
				unset($dataset);				
				exit;
				
			else:
				$this->response('No document id specified', 400);
			
				unset($json_params);
				unset($dataset);				
				exit;
				
			endif;
			
		} else {
			$this->response('Not found data', 400);
			
			unset($json_params);
			exit;
		}		
	}
	
	// ADD NEW ON 04/04/2018
	public function reactivation_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['DocID'] !== ''):
				$result = $this->dbapi->reactivationHandled($dataset);
				$this->response(array('status' => $result, 'msg' => ($result) ? 'success':'failed'), 200);
				
				unset($result);
				unset($json_params);
				unset($dataset);
				exit;
			
			else:
				$this->response('No document id specified', 400);
				
				unset($json_params);
				unset($dataset);
				exit;
			
			endif;
			
		} else {
			$this->response('Not found data', 400);
			
			unset($json_params);
			exit;
		}
	}
	
	public function defend_addtopic_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['DocID'] !== ''):
				$result = $this->dbapi->addNewTopicData($dataset);
				$this->response(array('data' => $result['data'], 'status' => true, 'code' => 200, 'msg' => 'success.'), 200);
				
				unset($json_params);
				unset($dataset);
				unset($result);
				exit;
				
			else:
				$this->response(array('data' => [], 'status' => false, 'code' => 400, 'msg' => 'Authenication denied.'), 400);
			
				unset($json_params);
				unset($dataset);
				exit;
				
			endif;
			
		} else {
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Not found data.'), 400);		
			
			unset($json_params);
			exit;
		}		
	}
	
	public function defend_deletetopic_post() {
		$this->load->model('dbapi');
		
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {			
			$dataset = json_decode($json_params, TRUE);
			
			if($dataset['DocID'] !== ''):
				$result = $this->dbapi->deleteTopicData($dataset);
				$this->response(array('data' => $result['data'], 'status' => true, 'code' => 200, 'msg' => 'success.'), 200);
				
				unset($json_params);
				unset($dataset);
				unset($result);
				exit;
				
			else:
				$this->response(array('data' => [], 'status' => false, 'code' => 400, 'msg' => 'Authenication denied.'), 400);
			
				unset($json_params);
				unset($dataset);				
				exit;
				
			endif;
			
		} else {
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Not found data.'), 400);			
			unset($json_params);
			exit;
		}		
	}
	
	public function defend_note_get() {
		$this->load->model('dbapi');	
		
		$doc_id = $this->get('xid');	
		
		if(empty($doc_id)):
			$this->response(array('data' => array(), 'status' => false, 'code' => 400, 'msg' => 'No document id specified'), 400);		
		
			unset($doc_id);			
			exit;
			
		else:
			$action_note = $this->dbapi->loadActionNote($doc_id);
			$this->response($action_note['data'], 200);	
			
			unset($doc_id);			
			exit;
			
		endif;		
	}
	
	public function missingdoc_get() {
		$this->load->model('dbapi');
		
		$doc_id = $this->get('xid');
		
		if(empty($doc_id)):
			$this->response(array('data' => array(), 'status' => false, 'code' => 400, 'msg' => 'No document id specified'), 400);
			
			unset($doc_id);
			exit;
		
		else:
			$missing_list = $this->dbapi->loadMissingDoc($doc_id);
			$this->response($missing_list['data'], 200);
			
			unset($doc_id);
			exit;
		
		endif;
	}
	
	public function fileviewer_post() {
		$json_params = file_get_contents("php://input");
		
		if (strlen($json_params) > 0 && $this->isValidJSON($json_params)) {
			$dataset = json_decode($json_params, TRUE);
			
			if(!empty($dataset['Files'])):
				$path   = $dataset;
				$type   = pathinfo($path['Files'], PATHINFO_EXTENSION);
				$data   = file_get_contents($path['Files']);

				$this->response(base64_encode($data), 200);
				
				unset($path);
				unset($type);
				unset($data);
				unset($dataset);
				
				exit;
			else:
				unset($dataset);
				$this->response(null, 400);
				exit;
			endif;
			
		} else {
			$this->response(array('status' => false, 'code' => 400, 'msg' => 'Not found data.'), 400);
			unset($json_params);
			exit;
		}
		
	}
	
	// ADD NEW 04/04/2018
	public function activeItems_get() {
		$this->load->model('dbapi');
		
		$doc_id = $this->get('xid');
		$item_id = $this->get('topic');
		$state = $this->get('state');
		
		if(empty($doc_id) && empty($item_id)):
			$this->response(array('data' => array(), 'status' => false, 'code' => 400, 'msg' => 'No document id specified'), 400);
			
			unset($doc_id);
			exit;
		
		else:		
			$result = $this->dbapi->activeItemsForGenerationPDF(array('DocID' => $doc_id, 'DefendCode' => $item_id, 'State' => $state));
			
			$this->response(array('status' => $result, 'msg' => ($result) ? 'success':'failed'), 200);
			
			unset($doc_id);
			exit;
		
		endif;
	}
		
	private function isValidJSON($str) {
		json_decode($str);
		return json_last_error() == JSON_ERROR_NONE;
	}
	
}