<?php defined('BASEPATH') or exit('No direct script access allowed');

class Accounts
{
    private static $ci, $providers, $scopes, $ch, $user, $curl_header;
    private static $ns = 'accounts';

    const admin = 0;

    public function __construct()
    {
        self::init();
    }

    private static function init()
    {
        self::$ci =& get_instance();
        self::$ci->load->model('accounts/accounts_m');
        self::$ch = curl_init();
        self::_curl_opt('returntransfer', true);
        self::_curl_opt('verbose', true);
        self::_curl_opt('header', true);
        self::_curl_opt('ssl_verifypeer', false);
        self::_curl_opt('ssl_verifyhost', false);
        self::_curl_opt('autoreferer', true);
        self::_curl_opt('followlocation', true);

        self::$user = self::$ci->current_user->id;
    }

    public static function provider($slug)
    {
        $slug = self::_slugify($slug);
        if(!isset(self::$providers[$slug])){
            $provider = self::$ci->accounts_m->get_provider($slug);
            is_null($provider) or self::$providers[$slug] = $provider;
        }
        return isset(self::$providers[$slug])? self::$providers[$slug] : null;
    }

    public static function add_provider($name, $args)
    {
        $params = array(
            'name' => $name,
            'slug' => self::_slugify($name),
            'scope_sep' => ','
        );
        foreach($args as $key => $val)
        {
            switch($key)
            {
                case 'scopes':
                    $val = implode("\r\n", $val);
                    $key = 'default_scopes';
                case 'oauth_version':
                case 'auth_url':
                case 'token_url':
                case 'api_url':
                case 'scope_sep':
                    $params[$key] = $val;
                    break;
            }
        }

        self::$ci->streams->entries->insert_entry($params, 'providers', self::$ns);
    }

    public static function add_scope($provider, $scope)
    {
        $provider = self::provider($provider);
        self::$providers[$provider->slug]->scopes[] = $scope;
    }

    public static function set_user($id)
    {
        self::$user = (int) $id;
    }

    public static function account($provider)
    {
        $provider = self::provider($provider);
        return self::$ci->accounts_m->get_account(self::$user, $provider->slug);
    }

    public static function __callStatic($method, $args)
    {
        $provider = self::provider($method);

        if(is_null($provider)) return null;
        if(isset($args[0]))
        {
            $params = array(
                'access_token' => self::access_token($provider->slug)
            );
            isset($args[1]) and $params = array_merge($params, $args[1]);
            $url = rtrim($provider->api_url, '/').'/';
            $url .= trim($args[0], '/');

            $result = self::_do_curl($url, $params, 'get');
            return json_decode($result);
        }
    }

    public static function auth($provider, $key = null, $secret = null)
    {
        $provider = self::provider($provider);
        if(isset($_GET['code']))
        {
            $result = self::_do_curl($provider->token_url, array(
                'code' => $_GET['code'],
                'client_id' => $provider->client_key,
                'client_secret' => $provider->client_secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => rtrim(current_url(), '/').'/'
            ));

            $result = json_decode($result);

            if(isset($result->access_token))
            {
                $params = array(
                    'provider' => $provider->id,
                    'access_token' => $result->access_token,
                    'token_type' => $result->token_type,
                    'id_token' => $result->id_token,
                    'expiration' => date('Y-m-d G:i:s', now() + ((int) $result->expires_in * 100))
                );

                if(self::$ci->controller == 'admin')
                {
                    $params['user'] = (string) self::admin;
                }
                else
                {
                    is_logged_in() and $params['user'] = self::$ci->current_user->id;
                } 

                self::$ci->streams->entries->insert_entry($params, 'accounts', self::$ns);
            }
        }
        else
        {
            $account = self::account($provider->slug);
            if(!isset($account->access_token) or $account->access_token == '')
            {
                $args = array(
                    'client_id' => $provider->client_key,
                    'scope' => implode($provider->scope_sep, self::$providers[$provider->slug]->scopes),
                    'redirect_uri' => rtrim(current_url(), '/').'/',
                    'access_type' => 'offline',
                    'response_type' => 'code'
                );
                $sep = strpos('?', $provider->auth_url)? '&' : '?';
                redirect($provider->auth_url.$sep.str_replace('%2B','+',http_build_query($args)));
            }
        }
    }

    private static function access_token($provider)
    {
        $provider = self::provider($provider);
        $account = self::account($provider->slug);
        if(isset($account->access_token)){
            self::_curl_opt('httpheader', array('Authorization: '.$account->token_type.' '.$account->access_token));
            return $account->access_token;
        } 
        return null;
    }

    private static function _curl_opt($key, $val)
    {
        $key = 'CURLOPT_'.strtoupper($key);
        curl_setopt(self::$ch, constant($key), $val);
    }

    private static function _do_curl($url, $data = array(), $method = 'post')
    {
        switch($method)
        {
            case 'post':
                self::_curl_opt('post', true);
                self::_curl_opt('postfields', http_build_query($data));
                break;
            case 'get':
                self::_curl_opt('httpget', true);
                if(!empty($data))
                {
                    $sep = strpos('?', $url)? '&' : '?';
                    $url .= $sep.http_build_query($data);
                }
                break;
        }
        self::_curl_opt('url', $url);
        $result = curl_exec(self::$ch);
        $header_size = curl_getinfo(self::$ch, CURLINFO_HEADER_SIZE);
        self::$curl_header = trim(substr($result, 0, $header_size));
        return trim(substr($result, $header_size));
    }

    private static function _slugify($str)
    {
        return preg_replace('/[^a-z]+/','',strtolower($str));
    }
}
