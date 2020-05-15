<?php
class Course_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function check_course_exists($external_id)
    {
        $id = 0;

        $this->db->select('id');
        $this->db->where('external_id', $external_id);
        $qry = $this->db->get('course');
        $result = $qry->result_array();
        
        if(!empty($result)){
            $id = $result[0]['id'];
        }

        return $id;
    }

    public function insert_course($data)
    {
        $this->db->insert('course', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function update_course($data, $external_id)
    {
        $id = $this->check_course_exists($external_id);

        if($id > 0)
            $this->db->update('course', $data, array('id' => $id));
    }

    public function check_category_exists($external_id)
    {
        $id = 0;

        $this->db->select('id');
        $this->db->where('external_id', $external_id);
        $qry = $this->db->get('category_dialogedu');
        $result = $qry->result_array();
        
        if(!empty($result)){
            $id = $result[0]['id'];
        }

        return $id;
    }

    public function insert_category($data)
    {
        $this->db->insert('category_dialogedu', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function update_category($data, $external_id)
    {
        $this->db->update('category_dialogedu', $data, array('id' => $id));
    }

    public function truncate_credit($nid = 0){
        if($nid > 0){
            $this->db->where('nid', $nid);
            $this->db->delete('credits');
        }
    }

    public function insert_credit($data = array())
    {
        $this->db->insert('credits', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function truncate_quiz($nid = 0){
        if($nid > 0){
            $this->db->where('nid', $nid);
            $this->db->delete('quiz');
        }
    }

    public function insert_quiz($data = array())
    {
        $this->db->insert('quiz', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function truncate_webform($nid = 0){
        if($nid > 0){
            $this->db->where('nid', $nid);
            $this->db->delete('survey');
        }
    }

    public function insert_webform($data = array())
    {
        $this->db->insert('survey', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function truncate_report($nid = 0){
        if($nid > 0){
            $this->db->where('nid', $nid);
            $this->db->delete('certificate');
        }
    }

    public function insert_report($data = array())
    {
        $this->db->insert('certificate', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function truncate_page($nid = 0){
        if($nid > 0){
            $this->db->where('nid', $nid);
            $this->db->delete('page');
        }
    }

    public function insert_page($data = array())
    {
        $this->db->insert('page', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function truncate_download_data($nid = 0, $instance_id = 0){
        if($nid > 0 && $instance_id > 0){
            $this->db->where('external_id', $nid);
            $this->db->where('instance_id', $instance_id);
            $this->db->delete('download');
        }
    }

    public function insert_download_data($data = array())
    {
        $this->db->insert('download', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function truncate_upload_data($nid = 0){
        if($nid > 0){
            $this->db->where('external_id', $nid);
            $this->db->delete('upload');
        }
    }

    public function insert_upload_data($data = array())
    {
        $this->db->insert('upload', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function get_notadded_course_dialogedu(){
        $result = array();

        $this->db->select('site_id, title, description, external_id, program_id, term_id, enabled, blocked, allow_self_enrollment, enable_grades, enable_discussions, enable_announcements, enable_materials, enable_messages, enable_certificate, enable_accessibility');
        $this->db->where('course_added', '0');
        $qry = $this->db->get('course');
        $result = $qry->result_array();
        
        return $result;
    }

    public function get_course_doc_urls(){
        $result = array();

        $this->db->select('*');
        $qry = $this->db->get('download');
        $result = $qry->result_array();
        
        return $result;
    }
}
