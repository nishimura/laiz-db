<?php

namespace Laiz\Db\Condition;

interface WhereInterface {
    public function add(WhereInterface $where);
    public function getString();
    public function getParams();
    public function isNoParam();
}
