<?php defined('BASEPATH') or exit('No direct script access allowed');

class Accounts_m extends MY_Model
{
    private $params = array(
        'global' => array(
            'namespace' => 'accounts'
        ),
        'accounts' => array(
            'stream' => 'accounts'
        ),
        'providers' => array(
            'stream' => 'providers',
            'order_by' => 'name',
            'sort' => 'asc'
        )
    );

    public function __call($method, $args)
    {
        empty($args) and $args = array( array() );

        preg_match('/^(get|save|delete)_?(data|account|provider)(s?)$/', strtolower($method), $m);

        if(sizeof($m) > 3)
        {
            if($m[3] == '')
            {
                $m[1] .= '_single';
            }
            $m[2] .= 's';
            return $this->{'_'.$m[1]}($m[2], $args);
        }
        return null;
    }

    private function _get($stream, $args)
    {
        isset($args[0]) or $args[0] = array();
        is_array($args[0]) or $args[0] = array();
        $params = array_merge( $this->params['global'], $this->params[$stream], $args[0] );
        $result = $this->streams->entries->get_entries($params);
        return $this->{ '_' . $stream }( $result['entries'] );
    }

    private function _get_single($stream, $args)
    {
        switch($stream)
        {
            case 'accounts':
                $provider = $this->get_provider($args[1]);
                $where = 'user = '.$args[0].' AND provider = '.$provider->id;
                break;
            case 'providers':
                is_numeric($args[0]) or $args[0] = preg_replace( '/[^a-z]+/', '', strtolower($args[0]) );
                $where = (is_numeric($args[0])? 'id' : 'slug').' = \''.$args[0].'\'';
                break;
        }
        $result = $this->_get( $stream, array(array('where' => $where)) );
        return isset($result[0])? $result[0] : null;
    }

    private function _save($stream, $args)
    {

    }

    private function _delete($stream, $args)
    {

    }

    private function _data($stream, $args)
    {

    }

    private function _accounts($entries)
    {
        foreach($entries as &$e)
        {
            $e = (object) $e;
            $e->id = (int) $e->id;
        }

        return $entries;
    }

    private function _providers($entries)
    {
        foreach($entries as &$e)
        {
            $e = (object) $e;
            $e->id = (int) $e->id;
            $e->oauth_version = (int) $e->oauth_version['key'];
            $e->slug = preg_replace('/[^a-z]+/','',strtolower($e->name));
            $e->default_scopes = preg_split('/[\r\n]+/', $e->default_scopes);
            $e->scopes = $e->default_scopes;
            empty($e->scope_sep) and $e->scope_sep = ' ';
        }

        return $entries;
    }
}
