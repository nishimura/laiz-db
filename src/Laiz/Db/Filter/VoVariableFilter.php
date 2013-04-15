<?php

namespace Laiz\Db\Filter;

// used in laiz-framework filter
class VoVariableFilter implements \Laiz\Core\Filter\VariableFilterInterface
{
    private $db;
    public function __construct(\Laiz\Core\Di $di)
    {
        $this->db = $di->get('Laiz\Db\Db');
    }
    public function accept($content)
    {
        if (!is_object($content))
            return false;
        return preg_match('/^' . preg_quote('Laiz\Db\Vo\\') . '/', get_class($content));
    }
    public function cast($content, $request = null)
    {
        if (!is_array($request))
            return $content;

        $orm = $this->db->createByVo($content);
        $pkeyNames = $orm->getPkeyColumns();
        $pkeys = array();
        $fromDb = true;
        foreach ($pkeyNames as $pkeyName){
            if (!isset($request[$pkeyName]) || strlen($request[$pkeyName]) === 0){
                $fromDb = false;
                break;
            }
            $pkeys[] = $request[$pkeyName];
        }
        if ($fromDb){
            $vo = $orm->id($pkeys)
                ->result();
            if ($vo)
                $content = $vo;
        }
        foreach ($request as $k => $v){
            if (property_exists($content, $k))
                $content->$k = $v;
        }
        return $content;
    }
}
