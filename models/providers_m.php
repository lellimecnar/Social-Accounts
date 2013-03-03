<?php defined('BASEPATH') or exit('No direct script access allowed');

class Providers_m extends MY_Model
{
    public function get_providers()
    {
        $result = $this->streams->entries->get_entries(array(
            'stream' => 'providers',
            'namespace' => 'accounts',
            'order_by' => 'name',
            'sort' => 'asc'
        ));

        foreach($result['entries'] as &$e)
        {
            $e['oauth_version'] = (int) $e['oauth_version']['key'];
            $e['slug'] = preg_replace('/[^a-z]+/','',strtolower($e['name']));

            $e = (object) $e;
        }
        return $result['entries'];
    }
}

