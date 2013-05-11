<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Accounts
 *
 * @author  Lance Miller <lance@astolat.com>
 */
class Module_Accounts extends Module
{
    public $version = '1.0.0';

    // Our streams namespace
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
        $this->lang->load('accounts/accounts');
        $menu['lang:cp:nav_settings'] = array(
            'lang:cp:nav_settings' => 'admin/settings',
            'lang:accounts:title' => 'admin/accounts'
        );
    }

    public function install()
    {
        // Load our stuff
        $this->lang->load('accounts/accounts');
        $this->load->library('accounts/accounts');

        // Create our streams
        $this->streams->streams->add_stream(
            lang($this->ns.':providers'), 
            'providers', 
            $this->ns, 
            'social_', 
            null, 
            array(
                'title_column' => 'name', 
                'is_hidden' => true, 
                'view_options' => array('name', 'client_key', 'oauth_version')
            )
        );

        $this->streams->streams->add_stream(
            lang($this->ns.':accounts'), 
            'accounts', 
            $this->ns, 
            'social_', 
            null, 
            array(
                'title_column' => 'provider', 
                'is_hidden' => true, 
                'view_options' => array('provider', 'user')
            )
        );

        // Save the stream info for later
        $streams['providers'] = $this->streams->streams->get_stream('providers', $this->ns);
        $streams['accounts'] = $this->streams->streams->get_stream('accounts', $this->ns);

        // Set up our fields...
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
                'name' => 'lang:'.$this->ns.':field:auth_method',
                'slug' => 'auth_method', 
                'namespace' => $this->ns,
                'type' => 'choice',
                'extra' => array(
                    'choice_type' => 'dropdown',
                    'choice_data' => 'get : GET'."\r\n".'post : POST',
                    'default_value' => 'get'
                ),
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:api_method',
                'slug' => 'api_method', 
                'namespace' => $this->ns,
                'type' => 'choice',
                'extra' => array(
                    'choice_type' => 'dropdown',
                    'choice_data' => 'get : GET'."\r\n".'post : POST',
                    'default_value' => 'get'
                ),
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:token_return_type',
                'slug' => 'token_return_type', 
                'namespace' => $this->ns,
                'type' => 'choice',
                'extra' => array(
                    'choice_type' => 'dropdown',
                    'choice_data' => 
                        'json : JSON'."\r\n".
                        'query_string : Query String'."\r\n".
                        'xml : XML',
                    'default_value' => 'json'
                ),
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:api_return_type',
                'slug' => 'api_return_type', 
                'namespace' => $this->ns,
                'type' => 'choice',
                'extra' => array(
                    'choice_type' => 'dropdown',
                    'choice_data' => 
                        'json : JSON'."\r\n".
                        'query_string : Query String'."\r\n".
                        'xml : XML',
                    'default_value' => 'json'
                ),
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
                'name' => 'lang:'.$this->ns.':field:expiration',
                'slug' => 'expiration', 
                'namespace' => $this->ns,
                'type' => 'datetime',
                'assign' => 'accounts'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:refresh_token',
                'slug' => 'refresh_token', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'accounts'
            ),
        );

        // ...and add them
        $this->streams->fields->add_fields($fields);

        // Our default providers
        $providers = array(
            'Dropbox' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'auth_method' => 'post',
                'api_method' => 'get',
                'default_scopes' => array(

                )
            ),
            'Facebook' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://www.facebook.com/dialog/oauth',
                'token_url' => 'https://graph.facebook.com/oauth/access_token',
                'api_url' => 'https://graph.facebook.com',
                'token_return_type' => 'query_string',
                'default_scopes' => array(
                    'offline_access', 
                    'email', 
                    'read_stream'
                )
            ),
            'Flickr' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'auth_method' => 'post',
                'api_method' => 'get',
                'default_scopes' => array(

                )
            ),
            'Foursquare' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://foursquare.com/oauth2/authenticate',
                'token_url' => 'https://foursquare.com/oauth2/access_token',
                'api_url' => 'https://api.foursquare.com/v2',
                'auth_method' => 'post',
            ),
            'GitHub' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://github.com/login/oauth/authorize',
                'token_url' => 'https://github.com/login/oauth/access_token',
                'api_url' => 'https://api.github.com'
            ),
            'Google' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://accounts.google.com/o/oauth2/auth',
                'token_url' => 'https://accounts.google.com/o/oauth2/token',
                'api_url' => 'https://www.googleapis.com',
                'scope_sep' => ' ',
                'auth_method' => 'post',
                'default_scopes' => array(
                    'https://www.googleapis.com/auth/userinfo.profile', 
                    'https://www.googleapis.com/auth/userinfo.email'
                )
            ),
            'Instagram' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://api.instagram.com/oauth/authorize',
                'token_url' => 'https://api.instagram.com/oauth/access_token',
                'api_url' => '',
                'auth_method' => 'post',
                'scope_sep' => '+'
            ),
            'LinkedIn' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'auth_method' => 'post',
                'api_method' => 'get',
                'default_scopes' => array(

                )
            ),
            'MailChimp' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://login.mailchimp.com/oauth2/authorize',
                'token_url' => 'https://login.mailchimp.com/oauth2/token',
                'api_url' => '',
                'auth_method' => 'post'
            ),
            'PayPal' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize',
                'token_url' => 'https://api.paypal.com/v1/oauth2/token',
                'api_url' => 'https://api.paypal.com',
                'scope_sep' => ' ',
                'auth_method' => 'post',
                'default_scopes' => array(
                    'profile',
                    'email'
                )
            ),
            'SalesForce' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://login.salesforce.com/services/oauth2/authorize',
                'token_url' => 'https://login.salesforce.com/services/oauth2/token',
                'api_url' => 'https://ap1.salesforce.com/services/data',
                'auth_method' => 'post',
                'default_scopes' => array(
                    'api',
                    'web'
                )
            ),
            'SoundCloud' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://soundcloud.com/connect',
                'token_url' => 'https://api.soundcloud.com/oauth2/token',
                'api_url' => 'https://api.soundcloud.com',
                'auth_method' => 'post',
                'default_scopes' => array(
                    '*'
                )
            ),
            'Tumblr' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'auth_method' => 'post',
                'api_method' => 'get',
                'default_scopes' => array(

                )
            ),
            'Twitter' => array(
                'oauth_version' => 1,
                'auth_url' => '',
                'token_url' => '',
                'api_url' => '',
                'auth_method' => 'post',
                'api_method' => 'get',
                'default_scopes' => array(

                )
            ),
            'Windows Live' => array(
                'oauth_version' => 2,
                'auth_url' => 'https://oauth.live.com/authorize',
                'token_url' => 'https://oauth.live.com/token',
                'api_url' => 'https://apis.live.net/v5.0',
                'auth_method' => 'post',
                'default_scopes' => array(
                    'wl.basic',
                    'wl.emails'
                )
            ),
        );

        // Save them each
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
