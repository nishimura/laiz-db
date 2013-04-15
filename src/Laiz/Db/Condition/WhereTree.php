<?php

namespace Laiz\Db\Condition;

use Laiz\Db\Helper;

/**
 * Conditions Tree
 */
class WhereTree implements WhereInterface
{
    private $children = array();
    private $andOr;
    public function __construct($andOr){
        $this->andOr = $andOr;
    }
    public function add(WhereInterface $where){
        $this->children[] = $where;
        return $this;
    }

    public function getString(){
        if (count($this->children) === 0)
            return null;
        $strings = array();
        foreach ($this->children as $child){
            $strings[] = $child->getString();
        }
        return '(' . implode(' '.$this->andOr.' ', $strings) . ')';
    }
    public function isNoParam(){
        return false;
    }
    public function getParams(){
        $ret = array();
        foreach ($this->children as $child){
            if ($child->isNoParam())
                continue;
            $params = $child->getParams();
            if (is_array($params)){
                foreach($params as $param)
                    $ret[] = $param;
            }else{
                $ret[] = $params;
            }
        }
        return $ret;
    }

    public function eq($where){
        return $this->add(Helper::where('=', $where));
    }
    public function ne($where){
        return $this->add(Helper::where('<>', $where));
    }
    public function lt($where){
        return $this->add(Helper::where('<', $where));
    }
    public function le($where){
        return $this->add(Helper::where('<=', $where));
    }
    public function gt($where){
        return $this->add(Helper::where('>', $where));
    }
    public function ge($where){
        return $this->add(Helper::where('>=', $where));
    }


    public function like($where){
        return $this->add(Helper::like($where, true, true));
    }
    public function notLike($where){
        return $this->add(Helper::like($where, true, true, true));
    }
    public function starts($where){
        return $this->add(Helper::like($where, false, true));
    }
    public function notStarts($where){
        return $this->add(Helper::like($where, false, true, true));
    }
    public function ends($where){
        return $this->add(Helper::like($where, true, false));
    }
    public function notEnds($where){
        return $this->add(Helper::like($where, true, false, true));
    }

    public function isNull($where){
        return $this->add(Helper::noParamWhere('is null', $where));
    }
    public function isNotNull($where){
        return $this->add(Helper::noParamWhere('is not null', $where));
    }
}
