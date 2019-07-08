<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dbmodel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        
       $connected = $this->checkDatabaseConnection($this->db);  
       if(!$connected):
       		$this->db_reconnection();
       endif;
        
    }
    
    public function checkDatabaseConnection($_instants) {
    	$initialized = $_instants->initialize();  
    	if($initialized) return TRUE;    		
    	else return FALSE;    	
    }
    
	protected function db_reconnection() {
    	$reconnected = $this->db->initialize();    	
    	if(!$reconnected):
			if(!$this->db->initialize()) $this->db_reconnection();				
			else return $this->db;
    	else: 
    		return $this->db; 
    	endif;	
    }

    /**
     * @param $tablename
     * @param $fields
     * @param $conditions
     * @return array
     * @throws Exception
    */ 
    public function CIQuery($sql) {
    	
    	if(empty($sql)) {
    		throw new Exception("The argusment invalid, Please your check parameter was not empty value.");
    		
    	} else {
    		
    		$query = $this->db->query($sql);    		
    		if($query->num_rows() > 0) {
    			return array(
    					"data"   => $query->result_array(),
    					"status" => TRUE,
    					"msg"    => "Success Queries.."
    			);
    			 
    		} else {
    			return array(
    					"data"   => array(),
    					"status" => FALSE,
    					"msg"    => "Not found data."
    			);
    		}
    		
    	}
    		
    }
    
    public function getNumRecords($tablename, $fields, $conditions) {
    	if(empty($tablename)) {
    		throw new Exception("The syntax is occurrence issue received parameter table name error. Please you are checked arguments.");
    	
    	} else {
    		
    		if($conditions == FALSE) {
    		
    			$this->db->select($fields)->from($tablename);
    			$query = $this->db->get();
    			if($query->num_rows() > 0):
    				return $query->num_rows();
    			else:
    				return 0;
    			endif;
    		
    		} else {
    			
    			$this->db->select($fields)->from($tablename)->where($conditions);
    			$query = $this->db->get();
    			if($query->num_rows() > 0):
    				return $query->num_rows();
    			else:
    				return 0;
    			endif;
    			
    		}
    		
    		
    	}
    	
    }
    
    public function loadData($tablename, $fields, $conditions) {

        if(empty($tablename)) {
            throw new Exception("The syntax is occurrence issue received parameter table name error. Please you are checked arguments.");

        } else {
			
            if($conditions == FALSE) {

                $this->db->select($fields)
                         ->from($tablename);
                $query = $this->db->get();
                if($query->num_rows() > 0):
                    return array(
                        "data"   => $query->result_array(),
                        "status" => TRUE,
                    	"msg"    => "Success Queries.."
                    );
                else:
                    return array(
                    	"data"   => array(),
                        "status" => FALSE,
                        "msg"    => "Not found data."
                    );
                endif;

            } else {

                $this->db->select($fields)
                         ->from($tablename)
                         ->where($conditions);
                $query = $this->db->get();
                if($query->num_rows() > 0):
                    return array(
                        "data"   => $query->result_array(),
                        "status" => TRUE,
                    	"msg"    => "Success Queries.."
                    );
                else:
                    return array(
                        "data"   => array(),
                        "status" => FALSE,
                        "msg"    => "Not found data."
                    );
                endif;
            }


        }

    }
	
	public function loadDataIn($tablename, $fields, $conditions = array(), $option = array(), $with) {

        if(empty($tablename)) {
            throw new Exception("The syntax is occurrence issue received parameter table name error. Please you are checked arguments.");

        } else {
		
			if($option == FALSE) {
			
				if(strtoupper($with) == "IN") {				
					$this->db->select($fields)
	                         ->from($tablename)
	                         ->where_in($conditions[0], $conditions[1]);
					
	                $query = $this->db->get();
	                if($query->num_rows() > 0):
	                    return array(
	                        "data"   => $query->result_array(),
	                        "status" => TRUE,
	                    	"msg"    => "Success Queries.."
	                    );
	                else:
	                    return array(
	                        "data"   => array(),
	                        "status" => FALSE,
	                        "msg"    => "Not found data."
	                    );
	                endif;
			
				} else if(strtoupper($with) == "NOTIN") {				
					$this->db->select($fields)
							 ->from($tablename)
							 ->where_not_in($conditions[0], $conditions[1]);
					
					$query = $this->db->get();
					if($query->num_rows() > 0):
						return array(
							"data"   => $query->result_array(),
							"status" => TRUE,
							"msg"    => "Active Record Queries.."
						);
					else:
						return array(
							"data"   => array(),
							"status" => FALSE,
							"msg"    => "Not found data."
						);
					endif;				
				}
			
			} else {	
				
				if(strtoupper($with) == "IN") {				
					$this->db->select($fields)
	                         ->from($tablename)
	                         ->where_in($conditions[0], $conditions[1])
							 ->where($option);
					
	                $query = $this->db->get();
	                if($query->num_rows() > 0):
	                    return array(
	                        "data"   => $query->result_array(),
	                        "status" => TRUE,
	                    	"msg"    => "Success Queries.."
	                    );
	                else:
	                    return array(
	                        "data"   => array(0),
	                        "status" => FALSE,
	                        "msg"    => "Not found data."
	                    );
	                endif;
			
				} else if(strtoupper($with) == "NOTIN") {				
					$this->db->select($fields)
							 ->from($tablename)
							 ->where_not_in($conditions[0], $conditions[1])
							 ->where($option);
					
					$query = $this->db->get();
					if($query->num_rows() > 0):
						return array(
							"data"   => $query->result_array(),
							"status" => TRUE,
							"msg"    => "Success Queries.."
						);
					else:
						return array(
							"data"   => array(),
							"status" => FALSE,
							"msg"    => "Not found data."
						);
					endif;				
				}
			}
        }

    }

    /**
     * @param $tablename
     * @param $fields
     * @param $conditions
     * @param $like
     * @return string
     * @throws Exception
     */
    public function data_validation($tablename, $fields, $conditions, $like) {

        if(empty($tablename)) {
            throw new Exception("The execution functional is occurrence issue received parameter table name error. Please you are checked arguments.");

        } else {
            if(empty($like)) {
            	
                $this->db->select($fields)
                         ->from($tablename)
                         ->where($conditions);
                
                $query = $this->db->get();
                if($query->num_rows() > 0):
                	return TRUE;
                else:
                    return FALSE;
                endif;

            } else {
            	
                $this->db->select($fields)
                         ->from($tablename)
                         ->where($conditions)
                         ->like($like);
                
                $query = $this->db->get();
                if($query->num_rows() > 0):
                    return TRUE;
                else:
                    return FALSE;
                endif;
                
            }
        }
    }
    
    /**
     * @param $tablename
     * @param array $collections
     * @param array $condition
     * @param $joiner [table, condition, option]
     * @return string
     * @throws Exception
     */
    public function map($tablename, $collections = array(), $joiner = array(), $condition = array()) {
    	
    	if(empty($tablename)) {
    		throw new Exception("The execution functional is occurrence issue received parameter table name error. Please you are checked arguments.");
    
    	} else {
    
    		$this->db->select($collections)
		    		 ->from($tablename)
		    		 ->join($joiner['join'], $joiner['on'], $joiner['option'])
		    		 ->where($condition);
    		
    		$query = $this->db->get();
    		if($query->num_rows() > 0):
    		return array(
    				"data"   => $query->result_array(),
    				"status" => TRUE,
    				"msg"    => "Success Queries.."
    		);
    		else:
    		return array(
    				"data"   => array(0),
    				"status" => FALSE,
    				"msg"    => "Not found data."
    		);
    		endif;
    
    	}
    
    }
    
    /**
     * @param $tablename
     * @param array $collections
     * @param array $conditions
     * @param $events
     * @return bool
     * @throws Exception
     */
    public function exec($tablename, $collections = array(), $conditions = array(), $events) {
    
    	if(empty($tablename) || empty($collections)) {    
    		throw new Exception("The execution functional is occurrence issue received parameter table name error. Please you are checked arguments.");
    
    	} else {
    
    		try {    
    			switch(strtolower($events)) {    
    				case 'insert':    
    					$this->db->trans_begin();
    					$this->db->insert($tablename, $collections);
    					
    					if ($this->db->trans_status() === false) {
    						$this->db->trans_rollback();
    						return FALSE;
    
    					} else {
    						$this->db->trans_commit();
    						return TRUE;
    
    					}
    
    				break;
    				case 'update':
    
    					$this->db->trans_begin();
    					$this->db->where($conditions);
    					$this->db->update($tablename, $collections);
    					
    					if ($this->db->trans_status() === false) {
    						$this->db->trans_rollback();
    						return FALSE;
    
    					} else {
    						$this->db->trans_commit();
    						return TRUE;
    
    					}
    
    				break;    
    			}
    
    		} catch(Exception $e) {
    			error_log("Execution Errors: ".$e->getTrace());
    		}
    	}
    }
        
    public function deleteItems($tablename, $conditions = array()) {
    	if(empty($tablename)) {
    		throw new Exception("The execution functional is occurrence issue received parameter table name error. Please you are checked arguments.");
    		
    	} else {
    		
    		if(count($conditions) > 0) {    			
    			try { 
    				$this->db->delete($tablename, $conditions); 
    				return TRUE;
    				
    			} catch(Exception $e) {
	    			error_log("Execution Errors: ".$e->getTrace());
	    		}
    			
    		} else {
    			return FALSE;
    		}
    		
    	}
    	
    }
    
}