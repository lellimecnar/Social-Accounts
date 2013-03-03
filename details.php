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
                'name' => 'lang:'.$this->ns.':field:scope',
                'slug' => 'scope', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:access_token',
                'slug' => 'access_token', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:uid',
                'slug' => 'uid', 
                'namespace' => $this->ns,
                'type' => 'text',
                'assign' => 'providers'
            ),
            array(
                'name' => 'lang:'.$this->ns.':field:expiration',
                'slug' => 'expiration', 
                'namespace' => $this->ns,
                'type' => 'datetime',
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
            )
        );

        $this->streams->fields->add_fields($fields);

        $this->streams->fields->assign_field($this->ns, 'accounts', 'access_token');
        $this->streams->fields->assign_field($this->ns, 'accounts', 'uid');

        $providers = array(
            'Blooie' => 2,
            'Dropbox' => 1,
            'Facebook' => 2,
            'Flickr' => 1,
            'Foursquare' => 2,
            'GitHub' => 2,
            'Google' => 2,
            'Instagram' => 2,
            'LinkedIn' => 1,
            'MailChimp' => 2,
            'Mail.Ru' => 2,
            'SoundCloud' => 2,
            'Tumblr' => 1,
            'Twitter' => 1,
            'VKontakte' => 2,
            'Windows Live' => 2,
            'Yandex' => 2,
        );

        foreach($providers as $name => $version)
        {
            $this->streams->entries->insert_entry(array(
                'name' => $name,
                'oauth_version' => $version
            ), 'providers', $this->ns);
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
