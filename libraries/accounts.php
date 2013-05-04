<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Accounts
 *
 * @author  Lance Miller <lance@astolat.com>
 */
class Accounts
{
    private static $ci, 
        $providers, 
        $scopes, 
        $ch, 
        $user, 
        $curl_header;

    // Streams namespace
    private static $ns = 'accounts';

    // The Site User ID
    const site_user = 0;

    /**
     * Set up the $ci variable, cURL handler, and $user
     */
    public function __construct()
    {
        // Load the CI stuff
        self::$ci =& get_instance();
        self::$ci->load->model('accounts/accounts_m');

        // Get cURL ready
        self::$ch = curl_init();
        self::_curl_opts(array(
            'returntransfer' => true,
            'verbose' => true,
            'header' => true,
            'ssl_verifypeer' => false,
            'ssl_verifyhost' => false,
            'autoreferer' => true,
            'followlocation' => true
        ));

        // Get the current user
        self::$user = self::$ci->current_user->id;
    }

    /**
     * Get the provider information from the database
     *
     *      $provider = Accounts::provider('google');
     * 
     * @param  string $slug Provider slug
     * @return stdClass 
     */
    public static function provider($slug)
    {
        // Slugify the name
        $slug = url_title($slug, '_');
        if(!isset(self::$providers[$slug])){
            // Get it if it doesn't already exist
            $provider = self::$ci->accounts_m->get_provider($slug);
            is_null($provider) or self::$providers[$slug] = $provider;
        }
        return isset(self::$providers[$slug])? self::$providers[$slug] : null;
    }

    /**
     * Add a new provider to the database
     *
     *      Accounts::add_provider('Google', array(
     *          'oauth_version' => 2,
     *          'auth_url' => 'https://accounts.google.com/o/oauth2/auth',
     *          'token_url' => 'https://accounts.google.com/o/oauth2/token',
     *          'api_url' => 'https://www.googleapis.com',
     *          'scope_sep' => ' ',
     *          'auth_method' => 'post',
     *          'default_scopes' => array(
     *              'https://www.googleapis.com/auth/userinfo.profile', 
     *              'https://www.googleapis.com/auth/userinfo.email'
     *          )
     *      ));
     * 
     * @param string $name Title Case name of provider
     * @param array  $args Array of provider params
     */
    public static function add_provider($name, array $args)
    {
        // Set up some defaults
        $params = array(
            'name' => $name,
            'slug' => url_title($name, '_'),
            'scope_sep' => ','
        );

        // And parse through the available params
        foreach($args as $key => $val)
        {
            switch($key)
            {
                case 'default_scopes':
                    // Make it a string
                    $val = implode("\r\n", $val);
                case 'oauth_version':
                case 'auth_url':
                case 'token_url':
                case 'api_url':
                case 'auth_method':
                case 'api_method':
                case 'scope_sep':
                    $params[$key] = $val;
                    break;
            }
        }

        // Save it to the db
        self::$ci->streams->entries->insert_entry($params, 'providers', self::$ns);
    }

    /**
     * Add a scope for API usage
     *
     *      Accounts::add_scope('google', 'https://www.googleapis.com/auth/calendar');
     * 
     * @param string $provider Provider slug
     * @param string $scope
     */
    public static function add_scope($provider, $scope)
    {
        // Get the provider
        $provider = self::provider($provider);
        // Add the scope
        self::$providers[$provider->slug]->scopes[] = $scope;
    }

    /**
     * add multiple scopes at once for API usage
     *
     *      Accounts::add_scopes('google', array(
     *          'https://www.googleapis.com/auth/calendar',
     *          'https://www.googleapis.com/auth/plus.me'
     *      ));
     * 
     * @param string $provider Provider slug
     * @param array  $scope
     */
    public static function add_scopes($provider, array $scope)
    {
        // Get the provider
        $provider = self::provider($provider);
        // Add the scopes
        self::$providers[$provider->slug]->scopes = array_merge(self::$providers[$provider->slug]->scopes, $scope);
    }

    /**
     * Prepend a service URI to the API URL
     *
     *      Accounts::set_service('google', 'calendar/v3/calendars')
     * 
     * @param string $provider Provider slug
     * @param string $api_prepend
     */
    public function set_service($provider, $api_prepend)
    {
        // Get the provider
        $provider = self::provider($provider);
        // Sanitize and set the string to prepend
        self::$providers[$provider->slug]->api_prepend = trim($api_prepend, '/').'/';
    }

    /**
     * Manually set the user for authentication and API calls
     *
     *      Accounts::set_user($user->id);
     *
     * Use this to set user to the "Site" account:
     *
     *      Accounts::set_user(Accounts::site_user);
     * 
     * @param int $id
     */
    public static function set_user($id)
    {
        // Just set the user as an integer
        self::$user = (int) $id;
    }

    /**
     * Get account details from the database
     *
     *      $account = Accounts::account('google', $user->id);
     * 
     * @param  string $provider Provider slug
     * @param  int $user_id
     * @return stdClass
     */
    public static function account($provider, $user_id = null)
    {
        // Get the provider
        $provider = self::provider($provider);
        // Use the current user if it's not given
        is_null($user_id) or $user_id = self::$user;

        // And return the result
        return self::$ci->accounts_m->get_account($user_id, $provider->slug);
    }

    /**
     * Use a provider's slug as a method call
     *
     *      $events = Accounts::google($calendarID.'/events', array(
     *          'orderBy' => 'startTime',
     *          'singleEvents' => 'true'
     *      ));
     * 
     * @param  string $method Provider slug
     * @param  array  $args Method arguments
     * @return stdClass API result
     */
    public static function __callStatic($method, $args)
    {
        // Get the provider
        $provider = self::provider($method);

        // Return null if the provider doesn't exist
        if(is_null($provider)) return null;

        // We need a URI to fetch
        if(isset($args[0]))
        {
            // Make sure there's only one trailing slash
            $url = rtrim($provider->api_url, '/').'/';

            // If we have a service URI to prepend, we need to add that
            isset($provider->api_prepend) and $url .= $provider->api_prepend;

            // And add the URI for this call
            $url .= trim($args[0], '/');

            // Add the access_token
            $params = array(
                'access_token' => self::access_token($provider->slug)
            );

            // Get and return the result
            $result = self::_do_curl($url, $params, (isset($args[1]) && is_string($args[1]))? $args[1] : $provider->api_method);
            return json_decode($result);
        }
    }

    /**
     * Authorize and collect Tokens
     *
     *      Accounts::auth('google')
     *
     * Client ID and Client Secret are stored in the database,
     * but you can override them here.
     * 
     * @param  string $provider Provider slug
     * @param  string $key Client ID
     * @param  string $secret Client Secret
     */
    public static function auth($provider, $key = null, $secret = null)
    {
        // Get the provider
        $provider = self::provider($provider);

        // Do we have the code back from the provider?
        if(isset($_GET['code']))
        {
            // If so, then we need to get the token
            $result = self::_do_curl($provider->token_url, array(
                'code' => $_GET['code'],
                'client_id' => $provider->client_key,
                'client_secret' => $provider->client_secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => rtrim(current_url(), '/').'/'
            ), $provider->auth_method);

            $result = json_decode($result);

            // Did we get the token?
            if(isset($result->access_token))
            {
                // If so, then let's get it ready to save
                $params = array(
                    'provider' => $provider->id,
                    'access_token' => $result->access_token,
                    'token_type' => $result->token_type,
                    'expiration' => date('Y-m-d G:i:s', now() + ((int) $result->expires_in * 100))
                );

                if(self::$ci->controller == 'admin')
                {
                    // If we're doing this from an admin page, then 
                    // it's gonna be for the Site User
                    $params['user'] = (string) self::site_user;
                }
                else
                {
                    // If not, then let's try the current user
                    is_logged_in() and $params['user'] = self::$ci->current_user->id;
                } 

                // Save it
                self::$ci->streams->entries->insert_entry($params, 'accounts', self::$ns);
            }
        }
        else
        {
            // We don't have a code from the provider
            // So let's see if we have a token
            $account = self::account($provider->slug);
            if(!isset($account->access_token) or $account->access_token == '')
            {
                // Nope, no token. Let's build the redirect URL..
                $args = array(
                    'client_id' => $provider->client_key,
                    'scope' => implode($provider->scope_sep, self::$providers[$provider->slug]->scopes),
                    'redirect_uri' => rtrim(current_url(), '/').'/',
                    'access_type' => 'offline',
                    'response_type' => 'code'
                );
                $sep = strpos('?', $provider->auth_url)? '&' : '?';

                // ...and send them over
                redirect($provider->auth_url.$sep.str_replace('%2B','+',http_build_query($args)));
            }
        }
    }

    /**
     * Get or set the access token for the
     * specified provider
     * 
     * @param  string $provider
     * @return string Access token
     */
    private static function access_token($provider)
    {
        // Get the provider
        $provider = self::provider($provider);
        // Get the account
        $account = self::account($provider->slug);
        if(isset($account->access_token)){
            // We have a token, so set the Authorization Header and return the token
            self::_curl_opt('httpheader', array('Authorization: '.$account->token_type.' '.$account->access_token));
            return $account->access_token;
        }

        // No token...
        return null;
    }

    /**
     * Set a cURL option
     * 
     * @param  string $key
     * @param  string $val
     */
    private static function _curl_opt($key, $val)
    {
        // Convert $key into a CURLOPT Constant
        $key = 'CURLOPT_'.strtoupper($key);
        // And set it
        curl_setopt(self::$ch, constant($key), $val);
    }

    /**
     * Set multiple cURL options
     * 
     * @param  array $opts
     */
    private static function _curl_opts($opts)
    {
        foreach($opts as $key => $val)
        {
            // Convert $key into a CURLOPT Constant
            $key = 'CURLOPT_'.strtoupper($key);
            // And set it
            curl_setopt(self::$ch, constant($key), $val);
        }
    }

    /**
     * Execute the cURL object
     * 
     * @param  string $url The URL to get
     * @param  array  $data GET or POST data
     * @param  string $method HTTP method
     * @return string The cURL response
     */
    private static function _do_curl($url, array $data = array(), $method = 'post')
    {
        // Set up the HTTP method
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

        // Set the URL and Execute
        self::_curl_opt('url', $url);
        $result = curl_exec(self::$ch);

        // Pull out and save the header
        $header_size = curl_getinfo(self::$ch, CURLINFO_HEADER_SIZE);
        self::$curl_header = trim(substr($result, 0, $header_size));

        // Return just the content
        return trim(substr($result, $header_size));
    }
}
