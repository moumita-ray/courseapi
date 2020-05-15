<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {

	public function __construct()
	{
		set_time_limit(0);

		parent::__construct();

		$this->load->model('Course_model');
	}

	public function index()
	{
		$get_urls = $this->Course_model->get_course_doc_urls();
		
		if(!empty($get_urls)){
		 	foreach($get_urls as $url){
				$file_url 		= 	$url['file_url'];
				$dir            =  	$url['external_id'].'/'.$url['folder_to_save'].'/';
				$fileName       =  	basename($url['file_url']);
				$saveFilePath   =   $dir . $fileName;
				

				if (!file_exists($dir)) {
					mkdir($dir, 0777, true);
				}

				//exec("wget -o $saveFilePath $file_url");

				$ch 			= curl_init($file_url); 
				$fp          	= fopen($saveFilePath, 'wb');
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
		 	}
		}
	}
}