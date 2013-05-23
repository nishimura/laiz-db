<?php
/**
 * Utility of Iterator
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2012 Satoshi Nishimura
 */

namespace Laiz\Db;

use IteratorIterator;
use Traversable;

/**
 * Utility of Iterator
 *
 * usage:
 * <code><pre>
 *   $iterator = $db->from('Item')->order('itemId')->iterator();
 *   $iterator2 = new Filter($iterator, array('myclass', 'filter'));
 * </pre></code>
 *
 * callback function:
 * <code><pre>
 * class myclass {
 *  public static function filter($vo){
 *   $vo->status = myclass::codeToText($vo->statusCode);
 *   return $vo;
 *  }
 * }
 * </pre></code>
 *
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2012 Satoshi Nishimura
 */
class Filter extends IteratorIterator
{
    private $callback;
    public function __construct(Traversable $iterator, $callback)
    {
        parent::__construct($iterator);
        $this->callback = $callback;
    }

    public function current()
    {
        $arg = parent::current();
        return call_user_func($this->callback, $arg);
    }
}
