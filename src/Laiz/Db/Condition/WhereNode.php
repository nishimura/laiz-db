<?php

namespace Laiz\Db\Condition;

use Laiz\Db\Util;

/**
 * Single Condition
 */
class WhereNode implements WhereInterface
{
    private $op;
    private $name;
    private $value;
    private $noParam;
    public function __construct($op, $voName, $value, $isNoParam = false){
        $this->op = $op;
        $this->name = Util::toDbName($voName);
        $this->value = $value;
        $this->noParam = $isNoParam;
    }
    public function add(WhereInterface $where){
        $ret = new WhereTree('and');
        $ret->add($this);
        $ret->add($where);
        return $ret;
    }
    public function getString(){
        $ret = " $this->name $this->op ";
        if ($this->noParam)
            return $ret;

        return $ret . ' ? ';
    }
    public function isNoParam(){
        return $this->noParam;
    }
    public function getParams(){
        return $this->value;
    }
}
