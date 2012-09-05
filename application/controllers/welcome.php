<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	 
	 
	 public function __construct(){
         parent::__construct();
         $this->load->model('facebook_model');
         // Enable profiling: Turn this off on deploy
         //$this->output->enable_profiler(TRUE);
     }
     public function index()
     {
         $data['fb_data'] = $this->session->userdata('fb_data'); // This array contains all the user FB information
         if($data['fb_data']['me']):
            redirect(base_url().'home');
        else:
            $data['page'] = 'no_user';
             $data['title'] = 'FriendData';
             $this->load->view('wrapper', $data);
             
         endif; 
     }
     
     function home() 
     {
         $data['fb_data'] = $this->session->userdata('fb_data'); // This array contains all the user FB information
         
         if($data['fb_data']['me']):
             
             $friends = $this->facebook_model->get_friends();
             
             if($friends === true):
                 redirect(base_url().'review_friends/'.$data['fb_data']['uid']);
             endif;
             
             $data['page'] = 'home';
             $data['title'] = 'Friends';
             
             $data['friend_count'] = $this->facebook_model->check_user_friends($data['fb_data']['uid']);
             
             
             $this->load->view('wrapper', $data);
      
             $this->facebook_model->update_user($data['fb_data']['uid']);
        else:
            redirect(base_url());
        endif;
     }

     function start_friend_processing()
     {

         $data['fb_data'] = $this->session->userdata('fb_data'); // This array contains all the user FB information
         $friend_request = $this->facebook_model->insert_all_users($data['fb_data']['uid']);
         return '';
     }
     
     // JSON encode the FB_data for use with jquery on the frontend
     function get_logged_in_user() 
     {
         $data['fb_data'] = $this->session->userdata('fb_data');
         echo json_encode($data['fb_data']);
     }
     
     
     
     function review_friends($uid)
     {
          if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.1.7' || $uid == '1805047'):

          else:
                 $this->output->cache(60*24*4);
         endif;
         

         $data['fb_data'] = $this->session->userdata('fb_data'); // This array contains all the user FB information
         $data['var_name'] = 'map_data';
         
         if(!$data['fb_data']['me']) redirect(base_url());
         if($data['fb_data']['uid'] != $uid) redirect(base_url().'review_friends/'.$data['fb_data']['uid']);
         
         $uid = $data['fb_data']['me']['id'];
         $data['info'] = $this->facebook_model->friend_info($uid);
         //var_dump($data['info']);
         
         $data['page'] = 'friend_review';
         $data['title'] = 'Review you FriendData';
         $this->load->view('wrapper', $data);

     }

     function friends($uid)
     {
         
         if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.1.7'):
              
         else:
                $this->output->cache(60*24*4);
        endif;
              
         $data['fb_data'] = $this->session->userdata('fb_data'); 
         $data['profile'] = $this->facebook_model->get_user_info_for_friendview($uid);
         $data['info'] = $this->facebook_model->friend_info($uid);
         
         $data['page'] = 'user_review';
          $data['title'] = 'Review '.$data['profile']['name'].'\'s FriendData';
          $this->load->view('wrapper', $data);
     }
     function post_stats()
     {
         $data['fb_data'] = $this->session->userdata('fb_data');
          $uid = $data['fb_data']['me']['id'];
          
          $ret_obj = $this->facebook_model->post_stats($uid);
          redirect(base_url().'review_friends/'.$uid);
     }

	 function update_friends()
     {
         $update = $this->facebook_model->update_friends_user_info();
         var_dump($update);

     }
     function update_friend($uid)
     {
         $update = $this->facebook_model->get_user_info($uid);
         var_dump($update);

     }
     function update_blanks($uid)
     {
         $update = $this->facebook_model->update_friends_blank($uid);
         var_dump($update);

     }
     function parse_user($uid)
     {
         $update = $this->facebook_model->parse_user($uid);
     }
     function geolocated_addresses()
     {
         $update = $this->facebook_model->geolocated_addresses();
         //var_dump($update);
     }
     function friends_addresses($uid)
     {
         
         $data['fb_data'] = $this->session->userdata('fb_data');
           $uid = $data['fb_data']['me']['id'];
           
         $data['friends'] = $this->facebook_model->friends_addresses($uid);
         $data['friends_homes'] = $this->facebook_model->friends_addresses($uid, 'home_location_id');

         $data['page'] = 'map';
           $data['title'] = 'Map of FriendData';
           $this->load->view('wrapper', $data);
     }
     function friends_addresses_json($uid)
     {
         $this->output->set_content_type('application/json');
         
         $data['query'] = $this->facebook_model->friends_addresses($uid)->result();
         $data['var_name'] = 'map_data';
         $this->load->view('json', $data);
     }
     function test_call()
     {
         $data['fb_data'] = $this->session->userdata('fb_data');
         
         $this->facebook_model->insert_all_users($data['fb_data']['uid']);
     }
     function image_test($phrase)
     {
         $data['fb_data'] = $this->session->userdata('fb_data');
         
         $this->load->model('image_model');
         $this->image_model->image_creator($data['fb_data']['uid']);
         $data['phrase'] = $phrase;
         
         
         $this->load->view('image', $data);
     }
     function jpgrapher()
     {
         $this->load->library('jpgraph');
                 //$bar_graph = $this->jpgraph->grouped_bars();
                 //echo $bar_graph;
     }

}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */