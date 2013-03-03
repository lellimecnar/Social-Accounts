<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Events_Account
{
    protected $ci;
    
    public function __construct()
    {
        $this->ci =& get_instance();

        // authenticate users as they're created
        Events::register('post_user_register', array($this, 'authenticate_user'));
    }

    public function authenticate_user($user_id)
    {
        // do the authentication stuff
    }
 }
