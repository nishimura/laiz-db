<?php

require_once 'config.php';

/**
 * @package   Tsukiyo_Sample
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Insert
{
    private $db;
    public function __construct($db){
        $this->db = $db;
    }
    function insertSubSubItem($parentId){
        for ($i = 1; $i <= 2; $i++){
            $subsubitem = new Vo_SubSubItem();
            $subsubitem->subItemId = $parentId;
            $subsubitem->val = $parentId * 10 + $i;
            try {
                $this->db->save($subsubitem);
                echo "inserted sub sub item $subsubitem->val<br>\n";
            }catch (Tsukiyo_Exception $e){
                echo $e->getMessage();
            }
        }
    }
    function insertSubItem($parentId, $parentName){
        for ($i = 1; $i <= 3; $i++){
            $subitem = new Vo_SubItem();
            $subitem->itemId = $parentId;
            $subitem->name = "sub $i";
            if ($i % 3 == 0)
                $subitem->opt = "opt $i";
            try {
                $this->db->save($subitem);
                echo "inserted sub item $i<br>\n";
                if (!isset($_GET['skip']) ||
                    ($parentName === 'foo1' && $i % 3 == 1) ||
                    ($parentName === 'foo2' && $i % 3 == 0) ||
                    ($parentName === 'foo3' && $i % 3 == 2))
                    $this->insertSubSubItem($subitem->subItemId);
            }catch (Tsukiyo_Exception $e){
                echo $e->getMessage();
            }
        }
    }
    function insertItem(){
        for ($i = 1; $i <= 3; $i++){
            $item = new Vo_Item();
            $item->name = "foo$i";
            try {
                $this->db->save($item);
                echo "inserted item $i<br>\n";
                if (!isset($_GET['skip2']) || $i != 2)
                    $this->insertSubItem($item->itemId, $item->name);
            }catch (Tsukiyo_Exception $e){
                echo $e->getMessage();
            }
        }
    }
}
$db->begin();
$insert = new Insert($db);
$insert->insertItem();
$db->commit();
