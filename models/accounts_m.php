<?php defined('BASEPATH') or exit('No direct script access allowed');

class Accounts_m extends MY_Model
{
    public function get_accounts()
    {
        $result = $this->streams->entries->get_entries(array(
            'stream' => 'accounts',
            'namespace' => 'accounts'
        ));

        $return = array();

        foreach($result['entries'] as &$e)
        {
            if(!isset($return[$e['provider']['id']])) $return[$e['provider']['id']] = array();

            $return[$e['provider']['id']][] = (object) $e;
        }

        foreach($this->db
            ->select('id')
            ->get('providers')
            ->result() as $p)
        {
            if(!isset($return[$p->id])) $return[$p->id] = array();
        }

        return $return;
    }

}
