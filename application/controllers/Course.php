<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course extends CI_Controller {

	public function __construct()
	{
		set_time_limit(0);

		parent::__construct();

		$this->load->model('Course_model');
		$this->load->library('curl');
		
		$this->url = ETHOSCE_API_URL;
		$this->user = ETHOSCE_USER_ID;
		$this->pass = ETHOSCE_USER_PASSWORD;
		$this->siteid = DIALOGEDU_SITE_ID;

		$this->course_slug = '';
	}

	public function index()
	{
		$this->load->view('course');
	}

	public function get_course_data(){
		$result = $this->get_course_api();
		
		if(!empty($result)){
			$last = $result->last;
			$page_array = explode('page=', $last);
			$last_page = $page_array[1];

			if($last_page == 0){
				$list = $result->list;
				if(!empty($list)){
					foreach($list as $val){
						if(!empty($val->nid)){
							if(!empty($val->nid->id)){
								$nid = $val->nid->id;  

								$node_data = $this->get_node_details($nid);
								
								if(!empty($node_data)){
									$node_course_list = $node_data->list;
									if(!empty($node_course_list)){
										foreach($node_course_list as $val_course){
											$this->prepare_dialogedu_data($val_course);
										}
									}
				 				}
							}
						}

					}
				}
			}else{
				for($i=0; $i<=$last_page; $i++){
					$data = $this->get_course_api($i);
					if(!empty($data)){
						$list = $data->list;
						if(!empty($list)){
							foreach($list as $val){
								if(!empty($val->nid)){
									if(!empty($val->nid->id)){
										$nid = $val->nid->id;

										$node_data = $this->get_node_details($nid);
										if(!empty($node_data)){
											$node_course_list = $node_data->list;
											if(!empty($node_course_list)){
												foreach($node_course_list as $val_course){

													$this->prepare_dialogedu_data($val_course);
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function get_course_api($page = 0){
		if($page == 0)
		$this->curl->create("$this->url/course.json");
		else
		$this->curl->create("$this->url/course.json?page=$page");

		$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
		$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
		$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response = $this->curl->execute();
		$result = json_decode($response);

		return $result;
	}

	public function get_node_details($nid = 0){
		$node = array();

		if($nid > 0){
			$this->curl->create("$this->url/node.json?nid=$nid");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$node = json_decode($response);
		}

		return $node;
	}

	public function get_taxonomy_term_details($tid = 0){
		$taxonomy_term = array();

		if($tid > 0){
			$this->curl->create("$this->url/taxonomy_term.json?tid=$tid");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$taxonomy_term = json_decode($response);
		}

		return $taxonomy_term;
	}

	// public function category_data_prepare($category_id = 0){
	// 	$category_data = $this->get_taxonomy_term_details($category_id);
	// 	if(!empty($category_data)){
	// 		$category_list = $category_data->list;
	// 		if(!empty($category_list)){
	// 			foreach($category_list as $val_category){
	// 				$check_category_exist = $this->Course_model->check_category_exists($val_category->tid);
	// 				if($check_course_exist){
	// 					//update category
	// 				}else{
	// 					$parent_category_list = $val_category->parent;
	// 					if(!empty($parent_category_list)){
	// 						$check_parent_exist = $this->Course_model->check_category_exists($parent_category_list[0]->id);
	// 						if($check_course_exist){
	// 							//update category
	// 						}else{
	// 							$inserted_pid = $this->category_data_prepare($parent_category_list[0]->id);
	// 						}
	// 					}else{
	// 						$parent_id = 0;
	// 					}

	// 					$slug = str_replace(' ', '-', $val_category->title);
	// 					$slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $slug);

	// 					$insert_category_data['site_id'] = 1226;
	// 					$insert_category_data['name'] = $val_category->title;
	// 					$insert_category_data['slug'] = $slug;
	// 					$insert_category_data['external_id'] = $val_category->tid;
	// 					$insert_category_data['parent_id'] = $parent_id;
	// 					$insert_category_data['description'] = $val_category->description;

	// 					$cid = $this->Course_model->insert_category($insert_category_data);
	// 				}
	// 			}
	// 		}
	// 	}

	// 	return $cid;
	// }

	public function prepare_dialogedu_data($course_data = array()){
		if(!empty($course_data) && isset($course_data->type) && $course_data->type == 'course'){
			$course_status = $course_data->status;
			$course_date = $course_data->field_course_date;
			$time = strtotime('2020-01-01 00:00:00');
			if($course_status == 1){
				if(!empty($course_date)){
					$course_start_datetime = $course_date->value;
					echo '<pre>';
					print_r($course_data);
					if(!empty($course_start_datetime) && $course_start_datetime > $time){
						/** Course Category data operation section */
						// $course_category = $course_data->field_course_category;
						// if(!empty($course_category)){
						// 	foreach($course_category as $category){
						// 		$category_data = $this->category_data_prepare($category->id);
						// 		echo '<pre>'; print_r($category_data);
						// 	}
						// }

						/** Course data operation section */

						$check_course_exist = $this->Course_model->check_course_exists($course_data->nid);
						
						if(!empty($course_data->field_course_summary))
							$course_description = $course_data->field_course_summary->value;
						else
						$course_description = '';
						
						if(!empty($course_data->field_faculty_credentials))
							$course_faculty = $course_data->field_faculty_credentials->value;
						else
							$course_faculty = '';

						if(!empty($course_data->field_accreditation))
							$course_accreditation = $course_data->field_accreditation->value;
						else
							$course_accreditation = '';

						$description = '<style>

						.tab {
						overflow: hidden;
						}
						
						.tab button {
						background-color: inherit;
						float: left;
						border: 1px solid #ccc;
						outline: none;
						cursor: pointer;
						padding-top: 8px;
						padding-bottom: 8px;
						padding-right: 12px;
						padding-left: 12px;
						transition: 0.3s;
						font-size: 14px;
						border-radius: 5px;
						margin-bottom: 10px;
						margin-right: 12px;
						}
						
						/* Change background color of buttons on hover */
						.tab button:hover {
						background-color: #eeeeee;
						}
						
						/* Create an active/current tablink class */
						.tab button.active {
						color: #fff;    
						background-color: #08c;
						}
						
						/* Style the tab content */
						.tabcontent {
						display: none;
						padding: 6px 12px;
						border-top: none;
						}
						</style>
						<div class="tab">
						<button class="tablinks active" onclick="openCity(event, \'coursedescription\')">Course Description</button>
						<button class="tablinks" onclick="openCity(event, \'courseinstructor\')">Instructor</button>
						<button class="tablinks" onclick="openCity(event, \'accreditation\')">Accreditation</button>
						</div>
						
						<div id="coursedescription" class="tabcontent" style="display:block">
						<h3>Course Description</h3>
						<p>'.$course_description.'</p>
						</div>
						
						<div id="courseinstructor" class="tabcontent">
							<h3>Instructor</h3>
							<p>'.$course_faculty.'</p> 
							</div>
							
							<div id="accreditation" class="tabcontent">
							<h3>Accreditation</h3>
							<p>'.$course_accreditation.'</p>
							</div>
							
							<script>
							function openCity(evt, cityName) {
								var i, tabcontent, tablinks;
								tabcontent = document.getElementsByClassName("tabcontent");
								for (i = 0; i < tabcontent.length; i++) {
								tabcontent[i].style.display = "none";
								}
								tablinks = document.getElementsByClassName("tablinks");
								for (i = 0; i < tablinks.length; i++) {
								tablinks[i].className = tablinks[i].className.replace(" active", "");
								}
								document.getElementById(cityName).style.display = "block";
								evt.currentTarget.className += " active";
							}
							</script>';

						if($check_course_exist){
							$update_course_data['startdate'] = date('Y-m-d', $course_start_datetime);	
							$update_course_data['site_id'] = $this->siteid;
							$update_course_data['title'] = $course_data->title;
							$update_course_data['description'] = $description;

							$this->Course_model->update_course($update_course_data, $course_data->nid);
						}else{
							$insert_course_data['startdate'] = date('Y-m-d', $course_start_datetime);	
							$insert_course_data['site_id'] = $this->siteid;
							$insert_course_data['title'] = $course_data->title;
							$insert_course_data['description'] = $description;
							$insert_course_data['external_id'] = $course_data->nid;

							$this->Course_model->insert_course($insert_course_data);
						}

						$course_slug = str_replace(' ', '-', $course_data->title);
	 					$this->course_slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', strtolower($course_slug));

						/** Course Upload data */
						if(!empty($course_data->upload)){
							$upload = $course_data->upload;

							$this->Course_model->truncate_upload_data($course_data->nid);

							foreach($upload as $up_val){
								$upload_data['external_id'] = $course_data->nid;
								if(!empty($up_val->file)){
									$upload_data['file_uri'] = $up_val->file->uri;
									$upload_data['file_id'] = $up_val->file->id;
									$upload_data['file_resource'] = $up_val->file->resource;
									$upload_data['file_uuid'] = $up_val->file->uuid;
								}
								$upload_data['description'] = $up_val->description;
								$upload_data['display'] = $up_val->display;

								$this->Course_model->insert_upload_data($upload_data);
							}
						}

						/** Course Credit data */
						$course_credit_data = $this->get_course_credits($course_data->nid);

						/** Course Quiz data */
						$course_quiz_data = $this->get_course_quiz($course_data->nid);

						/** Course Servey data */
						$course_survey_data = $this->get_course_survey($course_data->nid);

						/** Course Report data */
						$course_report_data = $this->get_course_report($course_data->nid);

						/** Course Page data */
						$course_page_data = $this->get_course_page($course_data->nid);
					}
				}
			}
		}
	}

	public function get_course_credits($nid = 0){
		if($nid > 0){
			$this->curl->create("$this->url/course_credit.json?nid=$nid");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$course_credits = json_decode($response);

			//print_r($course_credits);

			if(!empty($course_credits)){
				$last = $course_credits->last;
				$page_array = explode('page=', $last);
				$last_page = $page_array[1];
				if($last_page == 0){
					$list = $course_credits->list;
					if(!empty($list)){
						$this->Course_model->truncate_credit($nid);

						foreach($list as $credit_list){
							$insert_credit['ccid'] = $credit_list->ccid;
							$insert_credit['nid'] = $credit_list->nid->id;
							if(!empty($credit_list->type)){
								$insert_credit['type'] = $credit_list->type->id;
							}
							$insert_credit['increments'] = $credit_list->increments;
							$insert_credit['min'] = $credit_list->min;
							$insert_credit['max'] = $credit_list->max;
							$insert_credit['enable_variable_credit'] = $credit_list->enable_variable_credit;
							$insert_credit['active'] = $credit_list->active;
							$insert_credit['code'] = $credit_list->code;
							$insert_credit['expiration_type'] = $credit_list->expiration_type;
							$insert_credit['expiration_date'] = $credit_list->expiration_date;
							$insert_credit['expiration_offset'] = $credit_list->expiration_offset;
							$insert_credit['pid'] = $credit_list->pid;
							$insert_credit['feeds_item_guid'] = $credit_list->feeds_item_guid;
							$insert_credit['feeds_item_url'] = $credit_list->feeds_item_url;
							$insert_credit['feed_nid'] = $credit_list->feed_nid;

							$this->Course_model->insert_credit($insert_credit);
						}
					}
				}else{
					$this->Course_model->truncate_credit($nid);

					for($i=0; $i<=$last_page; $i++){
						$this->curl->create("$this->url/course_credit.json?nid=$nid&page=$i");
						$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
						$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
						$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = $this->curl->execute();
						$credits = json_decode($res);

						if(!empty($credits)){
							$list = $credits->list;
							if(!empty($list)){
								foreach($list as $credit_list){
									$insert_credit['ccid'] = $credit_list->ccid;
									$insert_credit['nid'] = $credit_list->nid->id;
									if(!empty($credit_list->type)){
										$insert_credit['type'] = $credit_list->type->id;
									}
									$insert_credit['increments'] = $credit_list->increments;
									$insert_credit['min'] = $credit_list->min;
									$insert_credit['max'] = $credit_list->max;
									$insert_credit['enable_variable_credit'] = $credit_list->enable_variable_credit;
									$insert_credit['active'] = $credit_list->active;
									$insert_credit['code'] = $credit_list->code;
									$insert_credit['expiration_type'] = $credit_list->expiration_type;
									$insert_credit['expiration_date'] = $credit_list->expiration_date;
									$insert_credit['expiration_offset'] = $credit_list->expiration_offset;
									$insert_credit['pid'] = $credit_list->pid;
									$insert_credit['feeds_item_guid'] = $credit_list->feeds_item_guid;
									$insert_credit['feeds_item_url'] = $credit_list->feeds_item_url;
									$insert_credit['feed_nid'] = $credit_list->feed_nid;
		
									$this->Course_model->insert_credit($insert_credit);
								}
							}
						}
					}	
				}
			}
		}
	}

	public function get_course_report($nid = 0){
		if($nid > 0){
			$this->curl->create("$this->url/course_report.json?nid=$nid");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$course_report = json_decode($response);

			//print_r($course_report);

			if(!empty($course_report)){
				$last = $course_report->last;
				$page_array = explode('page=', $last);
				$last_page = $page_array[1];
				if($last_page == 0){
					$list = $course_report->list;
					if(!empty($list)){
						$this->Course_model->truncate_report($nid);

						foreach($list as $report_list){
							$insert_report['crid'] = $report_list->crid;
							$insert_report['nid'] = $nid;
							if(!empty($report_list->uid)){
								$insert_report['uid'] = $report_list->uid->id;
							}
							$insert_report['date_completed'] = $report_list->date_completed;
							$insert_report['updated'] = $report_list->updated;
							$insert_report['grade_result'] = $report_list->grade_result;
							$insert_report['section'] = $report_list->section;
							$insert_report['section_name'] = $report_list->section_name;
							$insert_report['complete'] = $report_list->complete;
							$insert_report['data'] = json_encode($report_list->data);
							if(!empty($report_list->coid)){
								$insert_report['coid'] = $report_list->coid->id;
							}
							$insert_report['feeds_item_guid'] = $report_list->feeds_item_guid;
							$insert_report['feeds_item_url'] = $report_list->feeds_item_url;
							$insert_report['feed_nid'] = $report_list->feed_nid;

							$this->Course_model->insert_report($insert_report);
						}
					}
				}else{
					$this->Course_model->truncate_report($nid);

					for($i=0; $i<=$last_page; $i++){
						$this->curl->create("$this->url/course_report.json?nid=$nid&page=$i");
						$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
						$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
						$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = $this->curl->execute();
						$reports = json_decode($res);
						
						if(!empty($reports)){
							$list = $reports->list;
							if(!empty($list)){
								foreach($list as $report_list){
									$insert_report['crid'] = $report_list->crid;
									$insert_report['nid'] = $nid;
									if(!empty($report_list->uid)){
										$insert_report['uid'] = $report_list->uid->id;
									}
									$insert_report['date_completed'] = $report_list->date_completed;
									$insert_report['updated'] = $report_list->updated;
									$insert_report['grade_result'] = $report_list->grade_result;
									$insert_report['section'] = $report_list->section;
									$insert_report['section_name'] = $report_list->section_name;
									$insert_report['complete'] = $report_list->complete;
									$insert_report['data'] = json_encode($report_list->data);
									if(!empty($report_list->coid)){
										$insert_report['coid'] = $report_list->coid->id;
									}
									$insert_report['feeds_item_guid'] = $report_list->feeds_item_guid;
									$insert_report['feeds_item_url'] = $report_list->feeds_item_url;
									$insert_report['feed_nid'] = $report_list->feed_nid;
		
									$this->Course_model->insert_report($insert_report);
								}
							}
						}
					}	
				}
			}
		}
	}
	
	public function get_course_quiz($nid = 0){
		if($nid > 0){
			$this->curl->create("$this->url/course_object.json?nid=$nid&object_type=quiz");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$course_quiz = json_decode($response);

			//print_r($course_quiz);

			if(!empty($course_quiz)){
				$last = $course_quiz->last;
				$page_array = explode('page=', $last);
				$last_page = $page_array[1];
				if($last_page == 0){
					$list = $course_quiz->list;
					if(!empty($list)){
						$this->Course_model->truncate_quiz($nid);

						foreach($list as $quiz_list){
							$insert_quiz['field_time_to_complete'] = $quiz_list->field_time_to_complete;
							$insert_quiz['coid'] = $quiz_list->coid;
							$insert_quiz['nid'] = $nid;
							$insert_quiz['module'] = $quiz_list->module;
							$insert_quiz['title'] = $quiz_list->title;
							$insert_quiz['object_type'] = $quiz_list->object_type;
							$insert_quiz['enabled'] = $quiz_list->enabled;
							$insert_quiz['info'] = $quiz_list->info;
							$insert_quiz['instance'] = $quiz_list->instance;
							$insert_quiz['required'] = $quiz_list->required;
							$insert_quiz['weight'] = $quiz_list->weight;
							$insert_quiz['hidden'] = $quiz_list->hidden;
							$insert_quiz['duration'] = $quiz_list->duration;
							$insert_quiz['uuid'] = $quiz_list->uuid;
							$insert_quiz['data'] = $quiz_list->data;
							$insert_quiz['feeds_item_guid'] = $quiz_list->feeds_item_guid;
							$insert_quiz['feeds_item_url'] = $quiz_list->feeds_item_url;
							$insert_quiz['feed_nid'] = $quiz_list->feed_nid;
							if(!empty($quiz_list->instance_node)){
								$insert_quiz['instance_node'] = $quiz_list->instance_node->id;
							}

							$this->Course_model->insert_quiz($insert_quiz);

							if(!empty($quiz_list->instance)){
								$intance_id = $quiz_list->instance;

								$this->curl->create("$this->url/node.json?nid=$intance_id");
								$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
								$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
								$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
								$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
								$responsem = $this->curl->execute();
								$quiz_meterial = json_decode($responsem);
								//print_r($response->list);
								if(!empty($quiz_meterial->list)){
									foreach($quiz_meterial->list as $meterial){
										if(!empty($meterial->body)){
											$string = $meterial->body->value;
				
											$this->download($string, $nid, $intance_id);
										}
									}
								}
							}
						}
					}
				}else{
					$this->Course_model->truncate_quiz($nid);

					for($i=0; $i<=$last_page; $i++){
						$this->curl->create("$this->url/course_object.json?nid=$nid&object_type=quiz&page=$i");
						$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
						$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
						$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = $this->curl->execute();
						$quiz = json_decode($res);

						if(!empty($quiz)){
							$list = $quiz->list;
							if(!empty($list)){
								foreach($list as $quiz_list){
									$insert_quiz['field_time_to_complete'] = $quiz_list->field_time_to_complete;
									$insert_quiz['coid'] = $quiz_list->coid;
									$insert_quiz['nid'] = $nid;
									$insert_quiz['module'] = $quiz_list->module;
									$insert_quiz['title'] = $quiz_list->title;
									$insert_quiz['object_type'] = $quiz_list->object_type;
									$insert_quiz['enabled'] = $quiz_list->enabled;
									$insert_quiz['info'] = $quiz_list->info;
									$insert_quiz['instance'] = $quiz_list->instance;
									$insert_quiz['required'] = $quiz_list->required;
									$insert_quiz['weight'] = $quiz_list->weight;
									$insert_quiz['hidden'] = $quiz_list->hidden;
									$insert_quiz['duration'] = $quiz_list->duration;
									$insert_quiz['uuid'] = $quiz_list->uuid;
									$insert_quiz['data'] = $quiz_list->data;
									$insert_quiz['feeds_item_guid'] = $quiz_list->feeds_item_guid;
									$insert_quiz['feeds_item_url'] = $quiz_list->feeds_item_url;
									$insert_quiz['feed_nid'] = $quiz_list->feed_nid;
									if(!empty($quiz_list->instance_node)){
										$insert_quiz['instance_node'] = $quiz_list->instance_node->id;
									}

									$this->Course_model->insert_quiz($insert_quiz);

									if(!empty($quiz_list->instance)){
										$intance_id = $quiz_list->instance;
		
										$this->curl->create("$this->url/node.json?nid=$intance_id");
										$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
										$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
										$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
										$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
										$responsem = $this->curl->execute();
										$quiz_meterial = json_decode($responsem);
										//print_r($response->list);
										if(!empty($quiz_meterial->list)){
											foreach($quiz_meterial->list as $meterial){
												if(!empty($meterial->body)){
													$string = $meterial->body->value;
				
													$this->download($string, $nid, $intance_id);
												}
											}
										}
									}
								}
							}
						}
					}	
				}
			}
		}
	}

	public function get_course_survey($nid = 0){
		if($nid > 0){
			$this->curl->create("$this->url/course_object.json?nid=$nid&object_type=webform");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$course_webform = json_decode($response);

			//print_r($course_webform);
			if(!empty($course_webform)){
				$last = $course_webform->last;
				$page_array = explode('page=', $last);
				$last_page = $page_array[1];
				if($last_page == 0){
					$list = $course_webform->list;
					if(!empty($list)){
						$this->Course_model->truncate_webform($nid);

						foreach($list as $webform_list){
							$insert_webform['field_time_to_complete'] = $webform_list->field_time_to_complete;
							$insert_webform['coid'] = $webform_list->coid;
							$insert_webform['nid'] = $nid;
							$insert_webform['module'] = $webform_list->module;
							$insert_webform['title'] = $webform_list->title;
							$insert_webform['object_type'] = $webform_list->object_type;
							$insert_webform['enabled'] = $webform_list->enabled;
							$insert_webform['info'] = $webform_list->info;
							$insert_webform['instance'] = $webform_list->instance;
							$insert_webform['required'] = $webform_list->required;
							$insert_webform['weight'] = $webform_list->weight;
							$insert_webform['hidden'] = $webform_list->hidden;
							$insert_webform['duration'] = $webform_list->duration;
							$insert_webform['uuid'] = $webform_list->uuid;
							$insert_webform['data'] = $webform_list->data;
							$insert_webform['feeds_item_guid'] = $webform_list->feeds_item_guid;
							$insert_webform['feeds_item_url'] = $webform_list->feeds_item_url;
							$insert_webform['feed_nid'] = $webform_list->feed_nid;
							if(!empty($webform_list->instance_node)){
								$insert_webform['instance_node'] = $webform_list->instance_node->id;
							}

							$this->Course_model->insert_webform($insert_webform);

							if(!empty($webform_list->instance)){
								$intance_id = $webform_list->instance;

								$this->curl->create("$this->url/node.json?nid=$intance_id");
								$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
								$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
								$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
								$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
								$responsem = $this->curl->execute();
								$quiz_meterial = json_decode($responsem);
								//print_r($response->list);
								if(!empty($quiz_meterial->list)){
									foreach($quiz_meterial->list as $meterial){
										if(!empty($meterial->body)){
											$string = $meterial->body->value;
				
											$this->download($string, $nid, $intance_id);
										}
									}
								}
							}
						}
					}
				}else{
					$this->Course_model->truncate_webform($nid);

					for($i=0; $i<=$last_page; $i++){
						$this->curl->create("$this->url/course_object.json?nid=$nid&object_type=webform&page=$i");
						$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
						$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
						$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = $this->curl->execute();
						$webform = json_decode($res);

						if(!empty($webform)){
							$list = $webform->list;
							if(!empty($list)){
								foreach($list as $webform_list){
									$insert_webform['field_time_to_complete'] = $webform_list->field_time_to_complete;
									$insert_webform['coid'] = $webform_list->coid;
									$insert_webform['nid'] = $nid;
									$insert_webform['module'] = $webform_list->module;
									$insert_webform['title'] = $webform_list->title;
									$insert_webform['object_type'] = $webform_list->object_type;
									$insert_webform['enabled'] = $webform_list->enabled;
									$insert_webform['info'] = $webform_list->info;
									$insert_webform['instance'] = $webform_list->instance;
									$insert_webform['required'] = $webform_list->required;
									$insert_webform['weight'] = $webform_list->weight;
									$insert_webform['hidden'] = $webform_list->hidden;
									$insert_webform['duration'] = $webform_list->duration;
									$insert_webform['uuid'] = $webform_list->uuid;
									$insert_webform['data'] = $webform_list->data;
									$insert_webform['feeds_item_guid'] = $webform_list->feeds_item_guid;
									$insert_webform['feeds_item_url'] = $webform_list->feeds_item_url;
									$insert_webform['feed_nid'] = $webform_list->feed_nid;
									if(!empty($webform_list->instance_node)){
										$insert_webform['instance_node'] = $webform_list->instance_node->id;
									}

									$this->Course_model->insert_webform($insert_webform);

									if(!empty($webform_list->instance)){
										$intance_id = $webform_list->instance;
		
										$this->curl->create("$this->url/node.json?nid=$intance_id");
										$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
										$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
										$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
										$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
										$responsem = $this->curl->execute();
										$quiz_meterial = json_decode($responsem);
										//print_r($response->list);
										if(!empty($quiz_meterial->list)){
											foreach($quiz_meterial->list as $meterial){
												if(!empty($meterial->body)){
													$string = $meterial->body->value;
				
													$this->download($string, $nid, $intance_id);
												}
											}
										}
									}
								}
							}
						}
					}	
				}
			}
		}
	}

	public function get_course_page($nid = 0){
		if($nid > 0){
			$this->curl->create("$this->url/course_object.json?nid=$nid&object_type=course_page");
			$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
			$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
			$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response = $this->curl->execute();
			$course_page = json_decode($response);

			//print_r($course_page);

			if(!empty($course_page)){
				$last = $course_page->last;
				$page_array = explode('page=', $last);
				$last_page = $page_array[1];
				if($last_page == 0){
					$list = $course_page->list;
					if(!empty($list)){
						$this->Course_model->truncate_page($nid);

						foreach($list as $page_list){
							$insert_page['field_time_to_complete'] = $page_list->field_time_to_complete;
							$insert_page['coid'] = $page_list->coid;
							$insert_page['nid'] = $nid;
							$insert_page['module'] = $page_list->module;
							$insert_page['title'] = $page_list->title;
							$insert_page['object_type'] = $page_list->object_type;
							$insert_page['enabled'] = $page_list->enabled;
							$insert_page['info'] = $page_list->info;
							$insert_page['instance'] = $page_list->instance;
							$insert_page['required'] = $page_list->required;
							$insert_page['weight'] = $page_list->weight;
							$insert_page['hidden'] = $page_list->hidden;
							$insert_page['duration'] = $page_list->duration;
							$insert_page['uuid'] = $page_list->uuid;
							$insert_page['data'] = $page_list->data;
							$insert_page['feeds_item_guid'] = $page_list->feeds_item_guid;
							$insert_page['feeds_item_url'] = $page_list->feeds_item_url;
							$insert_page['feed_nid'] = $page_list->feed_nid;
							if(!empty($page_list->instance_node)){
								$insert_page['instance_node'] = $page_list->instance_node->id;
							}

							$this->Course_model->insert_page($insert_page);

							if(!empty($page_list->instance)){
								$intance_id = $page_list->instance;

								$this->curl->create("$this->url/node.json?nid=$intance_id");
								$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
								$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
								$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
								$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
								$responsem = $this->curl->execute();
								$quiz_meterial = json_decode($responsem);
								//print_r($response->list);
								if(!empty($quiz_meterial->list)){
									foreach($quiz_meterial->list as $meterial){
										if(!empty($meterial->body)){
											$string = $meterial->body->value;
				
											$this->download($string, $nid, $intance_id);
										}
									}
								}
							}
						}
					}
				}else{
					$this->Course_model->truncate_page($nid);

					for($i=0; $i<=$last_page; $i++){
						$this->curl->create("$this->url/course_object.json?nid=$nid&object_type=course_page&page=$i");
						$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
						$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
						$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
						$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = $this->curl->execute();
						$page = json_decode($res);

						if(!empty($page)){
							$list = $page->list;
							if(!empty($list)){
								foreach($list as $page_list){
									$insert_page['field_time_to_complete'] = $page_list->field_time_to_complete;
									$insert_page['coid'] = $page_list->coid;
									$insert_page['nid'] = $nid;
									$insert_page['module'] = $page_list->module;
									$insert_page['title'] = $page_list->title;
									$insert_page['object_type'] = $page_list->object_type;
									$insert_page['enabled'] = $page_list->enabled;
									$insert_page['info'] = $page_list->info;
									$insert_page['instance'] = $page_list->instance;
									$insert_page['required'] = $page_list->required;
									$insert_page['weight'] = $page_list->weight;
									$insert_page['hidden'] = $page_list->hidden;
									$insert_page['duration'] = $page_list->duration;
									$insert_page['uuid'] = $page_list->uuid;
									$insert_page['data'] = $page_list->data;
									$insert_page['feeds_item_guid'] = $page_list->feeds_item_guid;
									$insert_page['feeds_item_url'] = $page_list->feeds_item_url;
									$insert_page['feed_nid'] = $page_list->feed_nid;
									if(!empty($page_list->instance_node)){
										$insert_page['instance_node'] = $page_list->instance_node->id;
									}

									$this->Course_model->insert_page($insert_page);

									if(!empty($page_list->instance)){
										$intance_id = $page_list->instance;
		
										$this->curl->create("$this->url/node.json?nid=$intance_id");
										$this->curl->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
										$this->curl->option(CURLOPT_USERPWD, "$this->user:$this->pass");
										$this->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
										$this->curl->option(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
										$responsem = $this->curl->execute();
										$quiz_meterial = json_decode($responsem);
										//print_r($response->list);
										if(!empty($quiz_meterial->list)){
											foreach($quiz_meterial->list as $meterial){
												if(!empty($meterial->body)){
													$string = $meterial->body->value;
				
													$this->download($string, $nid, $intance_id);
												}
											}
										}
									}
								}
							}
						}
					}	
				}
			}
		}
	}

	public function download($string = "", $nid = 0, $instance_id = 0){
		if(!empty($string) && $nid > 0 && $instance_id > 0){
			preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $match);
			$urls = $match[0];

			if(!empty($urls)){
				$i = 0;
				$this->Course_model->truncate_download_data($nid, $instance_id);
				foreach($urls as $url){
					$i++;
					$insert_data['external_id'] = $nid;
					$insert_data['instance_id'] = $instance_id;
					$insert_data['occurance'] = $i;
					$insert_data['file_url'] = $url;
					$insert_data['folder_to_save'] = $this->course_slug;

					$this->Course_model->insert_download_data($insert_data);
				}
			}
		}
	}	

	
}
