<?php
/**
 * Conditions Helper
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2012 Satoshi Nishimura
 */

namespace Laiz\Db;

use Laiz\Db\Condition\WhereNode;
use Laiz\Db\Condition\WhereTree;

/**
 * Conditions Helper
 *
 * Make sub expression of sql
 *
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2012 Satoshi Nishimura
 */
class Helper
{
    const _AND = '_helper_and';
    const _OR  = '_helper_or';
    // shortcut: extract(Helper::$all);
    public static $and = array(__CLASS__, self::_AND);
    public static $or = array(__CLASS__, self::_OR);
    public static $all = array('and' => array(__CLASS__, self::_AND),
                               'or'  => array(__CLASS__, self::_OR));

    public static function _helper_and()
    {
        return new WhereTree('and');
    }

    function _helper_or(){
        return new WhereTree('or');
    }

    public static function like($where, $left, $right, $not = false){
        $where = self::prepareLike($where, $left, $right);
        if ($not)
            $op = 'not like';
        else
            $op = 'like';
        return self::where($op, $where);
    }
    public static function where($op, $where){
        $ret = null;
        foreach ($where as $k => $v){
            $w = new WhereNode($op, $k, $v);
            if ($ret)
                $ret = $ret->add($w);
            else
                $ret = $w;
        }
        return $ret;
    }
    public static function noParamWhere($op, $where){
        $ret = null;
        $where = (array)$where;
        foreach ($where as $k){
            $w = new WhereNode($op, $k, null, true);
            if ($ret)
                $ret = $ret->add($w);
            else
                $ret = $w;
        }
        return $ret;
    }
    public static function prepareLike($where, $left, $right){
        foreach ($where as $k => $v){
            $v = str_replace('\\', '\\\\', $v);
            $v = str_replace('%', '\\%', $v);
            $v = str_replace('_', '\\_', $v);
            if ($left)
                $v = '%' . $v;
            if ($right)
                $v = $v . '%';
            $where[$k] = $v;
        }
        return $where;
    }
}
