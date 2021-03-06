<?php
/**
 * Main Class
 *
 * PHP versions 5.3
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2012-2013 Satoshi Nishimura
 */

namespace Laiz\Db;

/**
 * Main Class
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Db
{
    protected $config = array('dsn' => '',
                              'createConfigEachTime' => true,
                              'configFile' => 'cache/tables.ini');

    /**
     * Set dsn
     */
    public function setDsn($dsn)
    {
        $this->config['dsn'] = $dsn;
        return $this;
    }
    /**
     * Set auto generate config file
     */
    public function setAutoConfig($auto)
    {
        $this->config['createConfigEachTime'] = $auto;
        return $this;
    }
    /**
     * Set config file path
     */
    public function setConfigFile($path)
    {
        $this->config['configFile'] = $path;
        return $this;
    }

    public function generateVo($name){
        return $this->create($name)->emptyVo();
    }

    public function from($name){
        return $this->create($name);
    }

    public function autoload($className){
        $prefix = __NAMESPACE__ . '\Vo\\';
        $pattern = preg_quote($prefix, '/');
        if (preg_match('/^' . $pattern . '/', $className)){
            $name = str_replace($prefix, '', $className);
            $this->generateVo($name);
        }
    }

    public function create($name){
        $driver = $this->getDriver();
        return new Orm($driver, $this->config['configFile'], $name,
                       $this->config['createConfigEachTime']);
    }

    public function createByVo($vo)
    {
        return $this->create($this->voToDbName($vo));
    }

    private function voToDbName($vo){
        $prefix = __NAMESPACE__ . '\Vo\\';
        return str_replace($prefix, '', get_class($vo));
    }
    public function save($vo){
        return $this->create($this->voToDbName($vo))->save($vo);
    }
    public function insert($vo){
        return $this->create($this->voToDbName($vo))->insert($vo);
    }
    public function update($vo){
        return $this->create($this->voToDbName($vo))->update($vo);
    }
    public function delete($vo, $ids = null){
        if (is_string($vo) && $ids){
            $orm = $this->create($vo);
            $arg = $ids;
        }else if ($vo instanceof Vo){
            $orm = $this->createByVo($vo);
            $arg = $vo;
        }else{
            throw new Exception('Unknown type of argument1');
        }
        return $orm->delete($arg);
    }
    public function begin(){
        return $this->getDriver()->begin();
    }
    public function commit(){
        return $this->getDriver()->commit();
    }
    public function abort(){
        return $this->getDriver()->abort();
    }

    public function getDriver(){
        try {
            $db = Driver\Factory::factory($this->config['dsn']);
            return $db;
        }catch (PDOException $e){
            // PDO error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }catch (Exception $e){
            // framework error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
