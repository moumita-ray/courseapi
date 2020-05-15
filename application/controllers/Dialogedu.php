<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dialogedu extends CI_Controller {

	public function __construct()
	{
		set_time_limit(0);

		parent::__construct();

		$this->load->model('Course_model');
		$this->load->library('curl');
		
		$this->url = DIALOGEDU_API_URL;
		$this->access_token = DIALOGEDU_ACCESS_TOKEN;
		$this->siteid = DIALOGEDU_SITE_ID;
	}

	public function index()
	{
		$get_courses = $this->Course_model->get_notadded_course_dialogedu();
		
		if(!empty($get_courses)){
		 	$i=0;
		 	foreach($get_courses as $course){
				$create_course = json_encode($course);                                                                                   
                                                                                                                  
				$ch = curl_init($this->url."/sites/$this->siteid/courses");                                                                      
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
				curl_setopt($ch, CURLOPT_POSTFIELDS, $create_course);                                                                  
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(      
					"Authorization: Bearer $this->access_token",                                                                    
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($create_course))                                                                       
				);
				$response = curl_exec($ch);
				curl_close ($ch);
				$info = json_decode($response); 
				
    			if(!empty($info)){
					if(isset($info->errors)){
						$error = $info->errors;
						//print_r($error);
					}else{
						$i++;
						$external_id = $course['external_id'];
						$data['course_added'] = '1';
						$this->Course_model->update_course($data, $external_id);
					}
				}
		 	}
		}
	}

	public function course_list(){
		$header = array("Authorization: Bearer $this->access_token");

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "$this->url/sites/$this->siteid/courses",
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		curl_close($curl);

		$response = json_decode($response, true);
		print_r($response);
	}

}