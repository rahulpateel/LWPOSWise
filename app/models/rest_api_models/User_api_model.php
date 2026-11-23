<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_api_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->config('ion_auth', TRUE);

        $this->hash_method = $this->config->item('hash_method', 'ion_auth');
        $this->store_salt = $this->config->item('store_salt', 'ion_auth');
        $this->salt_length = $this->config->item('salt_length', 'ion_auth');

        //load the bcrypt class if needed
        if ($this->hash_method == 'bcrypt') {
            if ($this->random_rounds) {
                $rand = rand($this->min_rounds, $this->max_rounds);
                $rounds = array('rounds' => $rand);
            } else {
                $rounds = array('rounds' => $this->default_rounds);
            }

            $this->load->library('bcrypt', $rounds);
        }

    }

    //create login process with username and password
    public function login_process($data){

        $row= $this->db->get_where('users',$data);
        if($row->num_rows()>0){
            $data = array('user_id' => $row->row()->id,
                'username'=> $row->row()->username,
                'password'=> $row->row()->password,
                'email'=> $row->row()->email,
                'active'=> $row->row()->active,
                'first_name'=> $row->row()->first_name,
                'last_name'=> $row->row()->last_name,
                'company'=> $row->row()->company,
                'phone'=> $row->row()->phone,
                'gender'=> $row->row()->gender,
                'warehouse_id'=> $row->row()->warehouse_id);
            return $data;
        }else{
            return false;
        }

    }

  
    //from ion_auth
    public function forgotten_password($identity)
    {    //changed $email to $identity
        if ($this->forgotten_passwordUpdateCode($identity)) {   //changed
            // Get user information
            $user = $this->getVanDriverByEmail($identity);

            if ($user) {
                $data = array(
                    'identity' => $identity,
                    'forgotten_password_code' => $user->forgotten_password_code
                );

                $this->load->library('parser');
                $parse_data = array(
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'reset_password_link' => anchor('reset_password/' . $user->forgotten_password_code, lang('reset_password')),
                    'site_link' => base_url(),
                    'site_name' => $this->Settings->site_name,
                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                );

                $msg = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/forgot_password.html');
                $message = $this->parser->parse_string($msg, $parse_data);
                $message = $message . "<br>" . lang('reset_password_link_alt') . "<br>" . site_url('reset_password/' . $user->forgotten_password_code);
                $subject = lang('email_forgotten_password_subject') . ' - ' . $this->Settings->site_name;

                try {
                    if ($this->sma->send_email($user->email, $subject, $message)) {
                        return TRUE;
                    } else {
                        return FALSE;
                    }
                } catch (Exception $e) {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }


    //from auth_model
    public function forgotten_passwordUpdateCode($identity)
    {
        if (empty($identity)) {
            return FALSE;
        }

        //All some more randomness
        $activation_code_part = "";
        if (function_exists("openssl_random_pseudo_bytes")) {
            $activation_code_part = openssl_random_pseudo_bytes(128);
        }

        for ($i = 0; $i < 1024; $i++) {
            $activation_code_part = sha1($activation_code_part . mt_rand() . microtime());
        }

        $key = $this->hash_code($activation_code_part . $identity);

        $update = array(
            'forgotten_password_code' => $key,
            'forgotten_password_time' => time()
        );

        $this->db->where('email',$identity);
        $sql=$this->db->update('lw_van_drivers',$update);

        return $sql;
    }

    //hash code  used in forgot password
    public function hash_code($password)
    {
        return $this->hash_password($password, FALSE, TRUE);
    }

    //hash password used in forgot password
    public function hash_password($password, $salt = false, $use_sha1_override = FALSE)
    {
        if (empty($password)) {
            return FALSE;
        }

        //bcrypt
        if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt') {
            return $this->bcrypt->hash($password);
        }

        if ($this->store_salt && $salt) {
            return sha1($password . $salt);
        } else {
            $salt = $this->salt();
            return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }
    }

    //create salt used in forgot password
    public function salt()
    {
        return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
    }

    public function getProfileData($id)
    {
       
        
        $query = $this->db->get_where('users', array('id' => $id), 1);
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;

    }



}
