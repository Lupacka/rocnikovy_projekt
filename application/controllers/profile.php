<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class profile extends CI_Controller {
  
  function __construct(){
    parent::__construct();
    }
	public function index()
	{
    #echo "Hi buzna <br>";
		$this->profile();
	}
  function profile($notification = ""){
    if($this->session->userdata('id')){  
      $data['title'] = ucfirst($this->session->userdata('nick'))."'s profile";  
      $this->load->model('get_db');
      $data['user_info'] = $this->get_db->get_user_info($this->session->userdata('id'));
    }else
      $data['title'] = "None's profile";
  	
    $this->load->model('registration');
    if(isset($notification))
      $data['notify'] = $notification;    

    $this->load->view('view_profile', $data);  
    
  }
  function upload_profile_img(){
    $this->load->helper('url');
    $this->load->library('upload.php');
    
    $config['upload_path'] = 'http://hudobniny.g6.cz/media';
		$config['allowed_types'] = 'gif|jpg|png';
    $config['file_name'] = $this->session->userdata('id');
		$config['max_size']	= '100';
		$config['max_width']  = '400';
		$config['max_height']  = '400';
    
    $this->upload->initialize($config); 
    $this->upload->set_allowed_types('*');   
    if (!$this->upload->do_upload('img')) 
			$this->profile($this->upload->display_errors()); 
   // echo display_errors();
   // $this->load->model('profile');
   // $this->profile->up_db_photo();
  }
  
  function update_profile(){
    $this->load->helper('url');
    $this->load->model('get_db');
    $this->load->library('form_validation');
    $config = array(
                array(
                  'field' => 'name', 'label' => 'Name', 'rules' => 'required|min_length[3]|max_length[15]|xss_clean|trim'      
                ) ,
                array(
                  'field' => 'surname', 'label' => 'Surname', 'rules' => 'required|min_lenghth[3]|max_length[15]|xss_clean|trim'   
                )
                ,array(
                  'field' => 'adress', 'label' => 'Adress', 'rules' => 'required|min_lenghth[10]|max_length[45]|xss_clean|trim|callback_check_adress'    
                )
                ,array(
                  'field' => 'country', 'label' => 'Country', 'rules' => 'required|xss_clean|trim'    
                )
                ,array(
                  'field' => 'p_number', 'label' => 'Phone number', 'rules' => 'required|min_lenghth[10]|max_length[20]|xss_clean|numeric|trim'    
                )
                ,array(
                  'field' => 'email', 'label' => 'Email', 'rules' => 'required|min_lenghth[5]|max_length[30]|xss_clean|valid_email|trim'    
                )    
    );
   
   $this->form_validation->set_rules($config); 
   if ($this->form_validation->run()){
    $data = array(
              array_map('strip_tags' ,array(
              'name' => $this->input->post('name'),   
              'surname' => $this->input->post('surname'),
              'adress' => $this->input->post('adress'),
              'country' => $this->input->post('country'),
              'p_number' => $this->input->post('p_number'),
              'email' =>  $this->input->post('email'),
              'id' => $this->session->userdata('id')
           )
          )
         );
    $this->get_db->update_user_info($data);
    $this->profile("Your personal information has been changed!");  
   }else{
    
    $this->profile();     
   } 
  
  }
  
  function check_adress(){
    $adress = $this->input->post('adress');
    $order = 1;
    $pom = "";
    for($i = 0; $i < strlen($adress); $i++){
      if($adress[$i] == ','){
        $pom = trim($pom);
        switch($order){
          case 1:
            if(strlen($pom) < 5 || strlen($pom) > 25)
              return false;
            break;
          case 2:
            if(!is_numeric($pom) || strlen($pom) != 5)
              return false;
            break; 
          case 3:
            if(strlen($pom) < 5 || strlen($pom) > 15)
              return false;
            break;
        }
        $pom = ""; 
        $order++; 
      }else
        $pom .= $adress[$i];
      
    } 
    return true;
   $this->form_validation->set_message('update_profle','Something wen wrong');
   return false;
  }
 function change_password(){
  
  $this->load->library('form_validation');
  $config = array(
               array(
                  'field' => 'pass_old', 'label' => 'Old Password', 'rules' => 'required|xss_clean|trim|callback_check_pass'      
                ),
                array(
                  'field' => 'pass_new', 'label' => 'Password', 'rules' => 'required|matches[pass_again]|min_length[6]|max_length[15]|xss_clean|trim'      
                ),
                array(
                  'field' => 'pass_again', 'label' => 'Repeat password', 'rules' => 'required|xss_clean|trim'      
                )                       
    );
  $this->form_validation->set_rules($config); 
  
  if ($this->form_validation->run()){
  
    $this->load->model('profile_mod');
    if($this->profile_mod->change_pass($this->input->post('pass_new'), $this->session->userdata('id')))
      $not = "Your password has been changed!!";
    else
      $not = validation_errors(); 
    $this->profile($not);
  }else{
   $this->profile();
  }             
 } 
 
  function check_pass(){
    $this->load->model('profile_mod');
    if($this->profile_mod->check_pass($this->session->userdata('nick')))
     return true;
    $this->form_validation->set_message("check_pass","Your old password doesn't match with password in database!!");
    return false;   
  }


}