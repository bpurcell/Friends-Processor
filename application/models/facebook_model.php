<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */


class Facebook_model extends CI_Model {

    public function __construct(){
        parent::__construct();

        $profile = null;

        $fb_config = $this->config->item('appId','secret');

         $this->load->library('facebook', $fb_config);
        

        // Get User ID
        $user = $this->facebook->getUser();

        // We may or may not have this data based on whether the user is logged in.
        //
        // If we have a $user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.

        $profile = null;
        if($user)
        {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $profile = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }


        $fb_data = array(
                        'me' => $profile,
                        'uid' => $user,
                        'loginUrl' => $this->facebook->getLoginUrl(
                            array(
                                'scope' => 'user_about_me, user_birthday, user_education_history, user_groups, user_hometown, user_interests, user_likes, user_location, user_relationships, user_relationship_details, user_religion_politics, user_work_history, user_photos, user_status, friends_about_me, friends_birthday, friends_education_history, friends_groups, friends_hometown, friends_interests, friends_likes, friends_location, friends_relationships, friends_relationship_details, friends_religion_politics, friends_work_history, friends_photos, friends_status,manage_friendlists,read_friendlists,publish_stream,read_stream', // app permissions
                                'redirect_uri' => base_url() // URL where you want to redirect your users after a successful login
                            )
                        ),
                        'logoutUrl' => $this->facebook->getLogoutUrl(),
                    );

         $this->session->set_userdata('fb_data', $fb_data);

    }
    function post_stats($uid)
    {
        $data['info'] = $this->facebook_model->friend_info($uid);
        
        $total_female = explode(".",(($data['info']['sex_breakdown'][0]['count']/$data['info']['total_friends'])*100));
         $total_female = $total_female[0];
         
         
         $message_str = "I have ".$data['info']['total_friends']." friends, ".$data['info']['friends_comparison']." who are more popular than me. On average you are ".$data['info']['average_age']." years old. ".$data['info']['most_mutual'][0]['name']." has the most mutual friends (".$data['info']['most_mutual'][0]['mutual_friend_count']."). ".$total_female."% of my friends are female.";

         return $this->facebook->api('/me/feed', 'POST',
                                     array(
                                       'link' => 'friends.porosventures.com/friends/'.$uid,
                                       'message' => $message_str
                                  ));
                                  
                                  
    }
    function get_friends()
    {
        $fb_data = $this->session->userdata('fb_data'); // This array contains all the user FB information
        
        $uid = $fb_data['me']['id'];
        
        $recent = $this->db->get_where('friends', array('uid1' => $uid), 1,0)->row();
        
        
        
        if(!$recent || $recent->created_on <= (time() - (4*24*60*60))): #4 days worth of difference
        #if($recent->created_on <= (time() - (10))): # 30 seconds
            
            $this->db->delete('friends', array('uid1' => $uid)); 
            
            $fql = $this->facebook->api( array(
                                         'method' => 'fql.query',
                                         'query' => 'SELECT uid1, uid2 FROM friend WHERE uid1 = me()',
              ));
              
              
              
              $count = 0;
              foreach($fql as $key=>$friend):
                  $count++;
                  $friend['created_on'] = time();
                  $this->db->set($friend);
  		          $query = $this->db->insert('friends', $friend);
  		          
  		          
  		          //if($count > 10) break;
  		          
  	          endforeach;
  		        
  		    return $count;
	    else:
	        return true;
	    endif;
    }
    
    function check_user_friends($uid)
    {
        $sql = "SELECT count(uid1) as count FROM friends f WHERE mutual_friend_count IS NULL AND uid1 = ?";
        $count = $this->db->query($sql, $uid)->row()->count;

        return $count;
    }
    
    function update_friends_blank($uid)
    {
        $sql = "SELECT * FROM friends f WHERE mutual_friend_count IS NULL AND uid1 = ?";
        $query = $this->db->query($sql, $uid)->result();
        
        foreach($query as $friend):
            $this->facebook_model->get_user_info($friend->uid2,$uid);
        endforeach;
        
        return true;
    }
    function update_friends_user_info($uid)
    {
        $query = $this->db->get_where('friends', array('uid1' => $uid))->result();

        foreach($query as $friend):
            $this->facebook_model->get_user_info($friend->uid2, $uid);
        endforeach;
        
        return true;
    }

    function insert_all_users($uid)
    {
        $friends = $this->facebook->api( array(
                                        'method' => 'fql.query',
                                        'query' => 'SELECT uid, name, birthday, birthday_date, education,family, relationship_status, significant_other_id, sex, religion, political, current_location, hometown_location, mutual_friend_count, friend_count FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me())',
             ));
             
             foreach($friends as $user):
                 $query = $this->db->get_where('users', array('uid' => $user['uid']))->num_rows();
                 
                 $mutual_friend_count = $user['mutual_friend_count'];
                 unset($user['mutual_friend_count']);
                  
                 if($query == 0):
                     
                     $user['family'] = serialize($user['family']);
                     $user['education'] = serialize($user['education']);
                     $user['current_location'] = serialize($user['current_location']);
                     $user['hometown_location'] = serialize($user['hometown_location']);
                     
             	     $query = $this->db->insert('users', $user);
                endif;
                
                 $this->db->update('friends', array('mutual_friend_count' => $mutual_friend_count), 'uid1 = '.$uid.' AND uid2 = '.$user['uid']);
                $this->facebook_model->parse_user($user['uid']);
            endforeach;
    }
    
    //  This function updates the current User and makes a PROFILE for them
    function update_user($uid)
    {
        $exists = $this->db->get_where('user_profile', array('uid' => $uid))->num_rows();
        if($exists == 1) return true;

            
            $user = $this->facebook->api( array(
                                            'method' => 'fql.query',
                                            'query' => 'SELECT uid, name, birthday, birthday_date, education,family, relationship_status, significant_other_id, sex, religion, political, current_location, hometown_location, mutual_friend_count, friend_count FROM user WHERE uid = '.$uid,
                 ));

            $user = $user[0];

            $user['family'] = serialize($user['family']);
            $user['education'] = serialize($user['education']);
            $user['current_location'] = serialize($user['current_location']);
            $user['hometown_location'] = serialize($user['hometown_location']);

            $mutual_friend_count = $user['mutual_friend_count'];
            unset($user['mutual_friend_count']);
            
    	    $query = $this->db->insert('user_profile', $user);
            
    	    $this->facebook_model->parse_user($uid, 'user_profile');

    }
    
    function parse_user($uid, $table = 'users')
    {
        $user = $this->db->get_where($table, array('uid' => $uid))->row();

        /* Family parse 
        if($user->family != 'a:0:{}' || $user->family != 'N;'):
            $family = unserialize($user->family);
            
            $sons = 0;
            $daughters = 0;
            $total_family = 0;
            foreach($family as $key=>$member):

                $data = array(
                            'uid1' => $uid,
                            'uid2' => $member['uid'],
                            'relationship' => $member['relationship']
                            );
                $this->db->insert('family', $data);
                
                if($member['relationship'] == 'son') $sons++;
                if($member['relationship'] == 'daughter') $daughters++;
                $total_family++;
            endforeach;
            
            $data = array(
                           'sons' => $sons,
                           'daughters' => $daughters,
                           'total_family' => $total_family
                        );
            $this->db->where('uid', $uid);
            $this->db->update('users', $data);
        endif;
        */
        
        /*/  education parse parse 
        if($user->education != 'a:0:{}' || $user->education != 'N;'):
            
            $education = unserialize($user->education);
            
            foreach($education as $key=>$school):
                $this->db->delete('education', array('uid' => $uid, 'school_id' => $school['school']['id'])); 
                
                if(isset($school['year']['name'])):
                    $data = array(
                                'uid' => $uid,
                                'school_id' => $school['school']['id'],
                                'type' => $school['type'],
                                'year' => $school['year']['name'],
                                'name' => $school['school']['name']
                                );
                else:
                $data = array(
                            'uid' => $uid,
                            'school_id' => $school['school']['id'],
                            'type' => $school['type'],
                            'name' => $school['school']['name']
                            );
                endif;
                $this->db->insert('education', $data);

            endforeach;

        endif;*/
        
        //  Location Parsing disabled  
        if($user->current_location != 'a:0:{}' || $user->current_location != 'N;'):
            
            $current = unserialize($user->current_location);
            
            $id = $current['id'];
            $query = $this->db->get_where('locations', array('id' => $id))->num_rows();
            
            if($query == 0 ):
                 
                 $url = $current['city']." ".$current['state'].", ".$current['country'];
                  $request='http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($url).'&sensor=false';

                  $json_data = file_get_contents($request);
                  $loc = json_decode($json_data)->results[0]->geometry->location;
                  
                  $data = array(
                              'id' => $current['id'],
                              'city' => $current['city'],
                              'state' => $current['state'],
                              'country' => $current['country'],
                              'lat' => $loc->lat,
                               'lon' => $loc->lng
                              );
                $this->db->insert('locations', $data);
            endif;
        
            $current_id = array('current_location_id' => $current['id']);
            $this->db->where('uid', $uid);
            $this->db->update('users', $current_id);

        endif;
        
        if($user->hometown_location != 'a:0:{}' || $user->hometown_location != 'N;'):
            $home = unserialize($user->hometown_location);
            
            $id = $home['id'];
                        
            $query = $this->db->get_where('locations', array('id' => $id))->num_rows();
            
            if($query == 0 ):
                 
                 $url = $home['city']." ".$home['state'].", ".$home['country'];
                  $request='http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($url).'&sensor=false';

                  $json_data = file_get_contents($request);
                  $loc = json_decode($json_data)->results[0]->geometry->location;
                  
                  $data = array(
                              'id' => $home['id'],
                              'city' => $home['city'],
                              'state' => $home['state'],
                              'country' => $home['country'],
                              'lat' => $loc->lat,
                               'lon' => $loc->lng
                              );
                $this->db->insert('locations', $data);

            endif;

            
            $home_id = array('home_location_id' => $home['id']);
            $this->db->where('uid', $uid);
            $query = $this->db->update('users', $home_id);
        endif;
        
    }
    
    // --------------------------------------------------
    //   Reporting Functions all below here.
    // --------------------------------------------------
    function friend_info($uid)
    {
        $data['total_friends'] = $this->facebook_model->total_friends($uid);
        $data['friends_comparison'] = $this->facebook_model->friends_comparison($uid);
        $data['friends_breakdown'] = $this->facebook_model->relationship_breakdown($uid);
        $data['age_breakdown'] = $this->facebook_model->age_breakdown($uid);
        $data['sex_breakdown'] = $this->facebook_model->sex_breakdown($uid);
        $data['most_mutual'] = $this->facebook_model->most_mutual($uid);
        $data['average_age'] = $this->facebook_model->average_age($uid);
        $data['education_breakdown'] = $this->facebook_model->education_breakdown($uid);
        $data['college_popularity'] = $this->facebook_model->college_popularity($uid);
        $data['college_count'] = $this->facebook_model->college_count($uid);
        $data['friends_address_count'] = $this->facebook_model->friends_address_count($uid);

        return $data;
    }
    function get_user_info_for_friendview($uid)
    {

        $sql = "SELECT uid, name, sex FROM user_profile WHERE uid = ?";
        $query = $this->db->query($sql, $uid)->row_array();
        $query['names'] = explode(' ',$query['name']);
        if($query['sex'] == 'male'):
            $query['pronoun'] = 'He';
        elseif($query['sex'] == 'female'):
            $query['pronoun'] = 'He';
        else:
            $query['pronoun'] = 'They';
        endif;
            
        
        return $query;
    }
    function total_friends($uid)
    {
        $sql = "SELECT count(u.uid) as total_friends FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ?";
        $query = $this->db->query($sql, $uid)->row();

        return $query->total_friends;
    }
    function friends_comparison($uid)
    {
        $total = $this->facebook_model->total_friends($uid);
        
        $sql = "SELECT count(u.uid) as popular_friends FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ? AND friend_count >=  ?";
        $query = $this->db->query($sql, array($uid,$total))->row();
        return $query->popular_friends;
    }
    function relationship_breakdown($uid)
    {
        $sql = "SELECT count(u.uid) as count, relationship_status FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ? GROUP BY relationship_status HAVING relationship_status IS NOT NULL";
        $query = $this->db->query($sql, $uid)->result_array();
        
        return $query;
    }
    function age_breakdown($uid)
    {
        $sql = "SELECT COUNT(*) as count, floor(age/10)*10 as decade  FROM
        (SELECT uid, birthday_date, DATEDIFF(CURDATE(),STR_TO_DATE(birthday_date, '%m/%d/%Y'))/365 as age FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ? AND CHAR_LENGTH(birthday_date) = 10 GROUP BY u.uid ORDER BY age DESC) as ages GROUP BY decade HAVING decade IS NOT NULL";
        
        $query = $this->db->query($sql, $uid)->result_array();
        
        return $query;
    }
    function average_age($uid)
    {
        $sql = "SELECT TRUNCATE(avg(DATEDIFF(CURDATE(),STR_TO_DATE(birthday_date, '%m/%d/%Y'))/365),1) as average_age FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ? AND CHAR_LENGTH(birthday_date) = 10";
        
        $query = $this->db->query($sql, $uid)->row_array();
        
        return $query['average_age'];
    }
    function sex_breakdown($uid)
    {
        $sql = "SELECT count(uid) as count, sex FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ? AND sex IN ('female','male') GROUP BY sex";
        $query = $this->db->query($sql, $uid)->result_array();

        return $query;
    }
    function most_mutual($uid)
    {
        $sql = "SELECT * FROM friends f LEFT JOIN users u ON u.uid = f.uid2 WHERE f.uid1 = ? ORDER BY f.mutual_friend_count DESC LIMIT 0,6";
        $query = $this->db->query($sql, $uid)->result_array();

        return $query;
    }
    function education_breakdown ($uid)
    {
        $query = array();
        $sql = "SELECT count(u.uid) as count FROM friends f LEFT JOIN users u ON u.uid = f.uid2 LEFT JOIN education e ON e.uid = u.uid WHERE f.uid1 = ? AND e.type='Graduate School'";
        $query['grad'] = $this->db->query($sql, $uid)->result_array();
        
         $sql = "SELECT count(u.uid) as count FROM friends f LEFT JOIN users u ON u.uid = f.uid2 LEFT JOIN education e ON e.uid = u.uid WHERE f.uid1 = ? AND e.type='College'";
         $query['college'] = $this->db->query($sql, $uid)->result_array();
         return $query;
    }
    function college_popularity($uid)
    {
        $sql = "SELECT count(u.uid) as count, e.name FROM friends f LEFT JOIN users u ON u.uid = f.uid2 LEFT JOIN education e ON e.uid = u.uid WHERE f.uid1 = ? AND (e.type = 'College' OR e.type = 'Graduate School') GROUP BY e.school_id  ORDER BY count DESC LIMIT 0,20";
        $query = $this->db->query($sql, $uid)->result_array();
         return $query;
    }
    function college_count($uid)
    {
        $sql = "SELECT count(DISTINCT(e.name)) count FROM friends f LEFT JOIN users u ON u.uid = f.uid2 LEFT JOIN education e ON e.uid = u.uid WHERE f.uid1 = ? AND  (e.type = 'College' OR e.type = 'Graduate School')";
        $query = $this->db->query($sql, $uid);
         return $query->row_array();
    }
    function friends_addresses($uid,$location = 'current_location_id')
    {
        $sql = "SELECT u.name, l.* FROM friends f LEFT JOIN users u ON u.uid = f.uid2 LEFT JOIN locations l ON l.id = u.".$location." WHERE l.city IS NOT NULL AND f.uid1 = ? ";
        $query = $this->db->query($sql,array($uid));
        return $query;
    }
    function friends_address_count($uid,$location = 'current_location_id')
    {
        $sql = "SELECT  count(u.uid) as count FROM friends f LEFT JOIN users u ON u.uid = f.uid2 LEFT JOIN locations l ON l.id = u.".$location." WHERE l.city IS NOT NULL AND f.uid1 = ?";
        $query = $this->db->query($sql,array($uid))->row();
        return $query->count;
    }
    function geolocated_addresses()
    {
        $sql = "SELECT * FROM locations l WHERE city IS NOT NULL";
        $query = $this->db->query($sql)->result_array();
         
         foreach($query as $location):
             $url = $location['city']." ".$location['state'].", ".$location['country'];
             $request='http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($url).'&sensor=false';

             $json_data = file_get_contents($request);
             $loc = json_decode($json_data)->results[0]->geometry->location;
            
             $data_loc = array(
                            'lat' => $loc->lat,
                            'lon' => $loc->lng
                         );
             $this->db->where('id', $location['id']);
             $this->db->update('locations', $data_loc);
             
         endforeach;
         return $query;
    }
    function test_call()
    {
        
        $sql = "SELECT * FROM friends f LIMIT 0,5";
        $query = $this->db->query($sql, $uid)->result();
        
        $user = $this->facebook->api( array(
                                         'method' => 'fql.query',
                                         'query' => 'SELECT uid, name, pic_square FROM user WHERE uid = me()
                                         OR uid IN (SELECT uid2 FROM friend WHERE uid1 = me())',
              ));
              var_dump($user);
        
        foreach($user as $key=>$friend):
            var_dump($friend);
            if($key >4) break;
        endforeach;
        
        return true;
        
        

              
    }
    
    
    
    
    
    /* single user info checker --  DEPRECATED
    function get_user_info($uid, $you_id)
    {
        
        $this->db->delete('users', array('uid' => $uid)); 
        
        
         $user = $this->facebook->api( array(
                                         'method' => 'fql.query',
                                         'query' => 'SELECT uid, name, birthday, birthday_date, family, education, relationship_status, significant_other_id, sex, religion, political, current_location, hometown_location, mutual_friend_count, friend_count FROM user WHERE uid = '.$uid,
              ));
              
         $user = $user[0];
          
         $user['family'] = serialize($user['family']);
         $user['education'] = serialize($user['education']);
         $user['current_location'] = serialize($user['current_location']);
         $user['hometown_location'] = serialize($user['hometown_location']);
         
         
         
         $mutual_friend_count = $user['mutual_friend_count'];
         unset($user['mutual_friend_count']);
         
    	 $query = $this->db->insert('users', $user);
    	 
         $this->db->update('friends', array('mutual_friend_count' => $mutual_friend_count), 'uid1 = '.$you_id.' AND uid2 = '.$uid);
    	 
    	 $this->facebook_model->parse_user($uid);
    }*/
    
}

?>