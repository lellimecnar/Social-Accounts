<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Accounts extends Module
{
    public $version = '1.0.0';

    private $ns = 'accounts';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Social Accounts'
            ),
            'description' => array(
                'en' => ''
            ),
            'frontend' => true,
            'backend'  => true,
            'skip_xss' => true,
            'sections' => array(
                'accounts' => array(
                     'name' => 'accounts:accounts',
                     'uri' => 'admin/accounts'
                ),
                'providers' => array(
                     'name' => 'accounts:providers',
                     'uri' => 'admin/accounts/providers'
                )
            )
        );
    }

    public function admin_menu(&$menu)
    {
        $menu['lang:cp:nav_settings'] = array(
            'lang:cp:nav_settings' => 'admin/settings',
            'lang:accounts:title' => 'admin/accounts'
        );
    }

    public function install()
    {
        $this->lang->load('accounts/accounts');
        $this->load->library('accounts/accounts');

        $this->streams->streams->add_stream(lang($this->ns.':accounts'), 'accounts', $this->ns);
        $this->streams->streams->add_stream(lang($this->ns.':providers'), 'providers', $this->ns);

        $streams = array();

        $streams['accounts'] = $this->streams->streams->get_stream('accounts', $this->ns);
        $streams['providers'] = $this->streams->streams->get_stream('providers', $this->ns);

        $fields = array(
            array(
                'name' => 'lang:'.$this->ns.':field:name',
                'slug' => 'name',
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers',
                'required' => true
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:slug',
                'slug' => 'slug',
                'namespace' => $this->ns,
                'type' => 'slug',
                'extra' => array('slug_field' => 'name'),
                'assign' => 'providers',
                'required' => true
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:client_key',
                'slug' => 'client_key',
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:client_secret',
                'slug' => 'client_secret',
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:oauth_version',
                'slug' => 'oauth_version', 
                'namespace' => $this->ns,
                'type' => 'choice',
                'extra' => array('choice_data' => '1'."\r\n".'2', 'choice_type' => 'dropdown', 'default_value' => 2),
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:auth_url',
                'slug' => 'auth_url', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:token_url',
                'slug' => 'token_url', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:api_url',
                'slug' => 'api_url', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:default_scopes',
                'slug' => 'default_scopes', 
                'namespace' => $this->ns,
                'type' => 'textarea',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:scope_sep',
                'slug' => 'scope_sep', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:user',
                'slug' => 'user', 
                'namespace' => $this->ns,
                'type' => 'user',
                'assign' => 'accounts'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:provider',
                'slug' => 'provider', 
                'namespace' => $this->ns,
                'type' => 'relationship',
                'extra' => array('choose_stream' => $streams['providers']->id),
                'assign' => 'accounts'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:access_token',
                'slug' => 'access_token', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'accounts'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:token_type',
                'slug' => 'token_type', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'accounts'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:id_token',
                'slug' => 'id_token', 
                'namespace' => $this->ns,
                'type' => 'textarea',
                'assign' => 'accounts'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:expiration',
                'slug' => 'expiration', 
                'namespace' => $this->ns,
                'type' => 'datetime',
                'assign' => 'accounts'
            )
        );

        $this->streams->fields->add_fields($fields);

        $providers = array(
            'Blooie' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Dropbox' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Facebook' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Flickr' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Foursquare' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'GitHub' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Google' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://accounts.google.com/o/oauth2/auth',
                'token_url' => 'https://accounts.google.com/o/oauth2/token',
                'api_url' => 'https://www.googleapis.com',
                'scope_sep' => ' ',
                'scopes' => array(
                    'https://www.googleapis.com/auth/userinfo.profile', 
                    'https://www.googleapis.com/auth/userinfo.email'
                )
            ),
            'Instagram' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'LinkedIn' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'MailChimp' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Mail.Ru' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'SoundCloud' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Tumblr' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Twitter' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'VKontakte' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Windows Live' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
            'Yandex' => array(
                'oauth_version' => 2,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'scopes' => array(

                )
            ),
        );

        foreach($providers as $name => $data)
        {
            Accounts::add_provider($name, $data);
        }

        return true;
    }

    public function uninstall()
    {
        $this->streams->utilities->remove_namespace($this->ns);
        return true;
    }

    public function upgrade($old_version)
    {
        return true;
    }

    public function help()
    {
        return "No documentation has been added for this module.";
    }
}
/* End of file details.php */
