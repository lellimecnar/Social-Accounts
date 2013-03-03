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

        preg_match('/^(get|save|delete)_?(data|accounts|providers)$/', strtolower($method), $m);
        if(sizeof($m) == 3)
        {
            return $this->{'_'.$m[1]}($m[2], $args);
        }
        return null;
    }

    private function _get($stream, $args)
    {
        is_array($args[0]) or $args[0] = array();
        $params = array_merge( $this->params['global'], $this->params[$stream], $args[0] );
        $result = $this->streams->entries->get_entries($params);
        return $this->{ '_' . $stream }( $result['entries'] );
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
        $return = array();

        foreach($this->db
            ->select('id')
            ->get('providers')
            ->result() as $p)
        {
            $return[ $p->id ] = array();
        }

        foreach($entries as &$e)
        {
            $e = (object) $e;
            $return[ (int) $e->provider['id'] ][] = $e;
        }

        return $return;
    }

    private function _providers($entries)
    {
        foreach($entries as &$e)
        {
            $e = (object) $e;
            $e->oauth_version = (int) $e->oauth_version['key'];
            $e->slug = preg_replace('/[^a-z]+/','',strtolower($e->name));
        }

        return $entries;
    }

}
