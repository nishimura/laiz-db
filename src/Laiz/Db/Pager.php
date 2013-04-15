<?php
/**
 * Simple Pager
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2012-2013 Satoshi Nishimura
 */

namespace Laiz\Db;

use Zend\Http\PhpEnvironment\Request;

/**
 * Simple Pager
 *
 * Make pager link.
 *
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2012-2013 Satoshi Nishimura
 */
class Pager
{
    // internal parameters
    private $iterator;
    private $page;
    private $pageCount;
    private $counterName;
    private $limit;
    private $offset;
    private $count;
    private $pagerLength;

    // decoration parameters
    private $htmlFirst = '&lt;&lt;';
    private $htmlLast = '&gt;&gt;';
    private $decoratePrefix = ' ';
    private $decorateSuffix = ' ';
    private $decorateCurrentPrefix = ' ';
    private $decorateCurrentSuffix = ' ';
    private $decorateDisabledPrefix = ' ';
    private $decorateDisabledSuffix = ' ';

    private $renderEndsForce = false;

    private $uri;
    private $params;

    /**
     * @param $iterator Laiz\Db\Iterator
     * @param $pageCount Number of Row
     * @param $pagerLength Maximum count of pager links
     * @param $counterName The Name of request parameter for page number.
     */
    public function __construct($iterator, $pageCount, $pagerLength = 10, $counterName = 'n')
    {
        if (!method_exists($iterator, 'limit') ||
            !method_exists($iterator, 'offset') ||
            !method_exists($iterator, 'count'))
            trigger_error(get_class($iterator) . ' is not supported.',
                          E_USER_ERROR);

        $this->counterName = $counterName;
        $this->pageCount = $pageCount;
        $this->pagerLength = $pagerLength;
        $this->iterator = $iterator;

        if (isset($_GET[$counterName]) && is_numeric($_GET[$counterName]))
            $page = $_GET[$counterName];
        else if (isset($_POST[$counterName]) && is_numeric($_POST[$counterName]))
            $page = $_POST[$counterName];
        else
            $page = 0;

        $this->page = (int)$page;
        $this->limit = $pageCount;
        $this->offset = ($page) * $pageCount;
        $this->count = $iterator->count();

        $iterator->limit($this->limit)
            ->offset($this->offset);


        $request = new Request();
        $this->uri = $request->getUri()
            ->setScheme(null)
            ->setHost(null)
            ->setUserInfo(null)
            ->setPort(null);
        $this->params = $this->uri->getQueryAsArray();
    }
    public function setHtmlFirst($htmlFirst){
        $this->htmlFirst = $htmlFirst;
        return $this;
    }
    public function setHtmlLast($htmlLast){
        $this->htmlLast = $htmlLast;
        return $this;
    }
    public function setDecoratePrefix($prefix){
        $this->decoratePrefix = $prefix;
        $this->decorateCurrentPrefix = $prefix;
        return $this;
    }
    public function setDecorateSuffix($suffix){
        $this->decorateSuffix = $suffix;
        $this->decorateCurrentSuffix = $suffix;
        return $this;
    }
    public function setDecorateCurrentPrefix($prefix){
        $this->decorateCurrentPrefix = $prefix;
        return $this;
    }
    public function setDecorateCurrentSuffix($suffix){
        $this->decorateCurrentSuffix = $suffix;
        return $this;
    }
    public function setRenderEndsForce($render){
        $this->renderEndsForce = $render;
        return $this;
    }
    public function setDecorateDisabledPrefix($prefix){
        $this->decorateDisabledPrefix = $prefix;
        return $this;
    }
    public function setDecorateDisabledSuffix($suffix){
        $this->decorateDisabledSuffix = $suffix;
        return $this;
    }
    public function getHtml()
    {
        $last = (int)($this->count / $this->pageCount);
        if ($this->count % $this->pageCount == 0)
            $last--;
        if ($last <= 0)
            return '';

        $isFirst = $this->page == 0;
        $isLast = $this->page === $last;

        $start = $this->page - (int)($this->pagerLength / 2);
        if ($last - $start < $this->pagerLength)
            $start = $last - $this->pagerLength + 1;
        if ($start < 0)
            $start = 0;
        $max = min($start + $this->pagerLength - 1, $last);
        $is = array();
        for ($i = $start; $i <= $max; $i++){
            $is[] = $i;
        }

        $ret = '';

        if (!$isFirst || $this->renderEndsForce){
            if ($isFirst){
                $ret .= $this->decorateDisabledPrefix;
                $ret .= "<a>$this->htmlFirst</a>";
                $ret .= $this->decorateDisabledSuffix;
            }else{
                $ret .= $this->decoratePrefix;
                $ret .= '<a href="'
                    . $this->getRelativePath(0) . "\">$this->htmlFirst</a>";
                $ret .= $this->decorateSuffix;
            }
        }
        foreach ($is as $i){
            $viewCount = $i + 1;
            if ($i === $this->page)
                $ret .= $this->decorateCurrentPrefix . "<a>$viewCount</a>"
                    . $this->decorateCurrentSuffix;
            else
                $ret .= $this->decoratePrefix
                    . '<a href="'
                    . $this->getRelativePath($i) . "\">$viewCount</a>"
                    . $this->decorateSuffix;
        }
        if (!$isLast || $this->renderEndsForce){
            if ($isLast){
                $ret .= $this->decorateDisabledPrefix;
                $ret .= "<a>$this->htmlLast</a>";
                $ret .= $this->decorateDisabledSuffix;
            }else{
                $ret .= $this->decoratePrefix;
                $ret .= '<a href="'
                    . $this->getRelativePath($last) . "\">$this->htmlLast</a>";
                $ret .= $this->decorateSuffix;
            }
        }
        return $ret;













        $self = $_SERVER['SCRIPT_NAME'] . '?';
        foreach ($this->params as $name){
            if (!isset($_GET[$name]) || strlen($_GET[$name]) === 0)
                continue;

            $self .= $name . '=' . urlencode($_GET[$name]) . '&';
        }
        $self .= $this->counterName . '=';

        $ret = '';
        if (!$isFirst || $this->renderEndsForce){
            if ($isFirst){
                $ret .= $this->decorateDisabledPrefix;
                $ret .= "<a>$this->htmlFirst</a>";
                $ret .= $this->decorateDisabledSuffix;
            }else{
                $ret .= $this->decoratePrefix;
                $ret .= "<a href=\"${self}0\">$this->htmlFirst</a>";
                $ret .= $this->decorateSuffix;
            }
        }
        foreach ($is as $i){
            $viewCount = $i + 1;
            if ($i === $this->page)
                $ret .= $this->decorateCurrentPrefix . "<a>$viewCount</a>"
                    . $this->decorateCurrentSuffix;
            else
                $ret .= $this->decoratePrefix
                    . "<a href=\"$self$i\">$viewCount</a>"
                    . $this->decorateSuffix;
        }
        if (!$isLast || $this->renderEndsForce){
            if ($isLast){
                $ret .= $this->decorateDisabledPrefix;
                $ret .= "<a>$this->htmlLast</a>";
                $ret .= $this->decorateDisabledSuffix;
            }else{
                $ret .= $this->decoratePrefix;
                $ret .= "<a href=\"$self$last\">$this->htmlLast</a>";
                $ret .= $this->decorateSuffix;
            }
        }
        return $ret;
    }

    private function getRelativePath($counter)
    {
        $this->params[$this->counterName] = $counter;
        $this->uri->setQuery($this->params);
        return $this->uri->toString();
    }
}
