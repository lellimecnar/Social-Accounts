<?php defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('accounts');
        $this->load->model('accounts_m');
        $this->load->library('accounts/accounts');
    }

    public function index($method)
    {
        Accounts::auth('google');
        //Accounts::add_scope('google','https://www.googleapis.com/auth/calendar');
        Accounts::set_user(Accounts::admin);
        Accounts::google('oauth2/v1/userinfo');
        exit;

        if($method == 'index') $method = 'accounts';

        $this->template->active_section = $method;
        $view_path = 'admin/'.$method.'/index';

        $data = array('providers' => $this->accounts_m->get_providers());

        if($method != 'providers')
        {
            $data[$method] = $this->accounts_m->{ 'get_'.$method }();
        }

        $this->template
            ->title(lang('accounts:'.$method))
            ->append_css('module::accounts.css')
            ->build('admin/partials/blank_section', array('content' => $this->load->view($view_path, $data, true)));
    }
}
