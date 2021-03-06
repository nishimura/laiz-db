<?php
/**
 * Database Driver Class File
 *
 * PHP versions 5.3
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2012-2013 Satoshi Nishimura
 */

namespace Laiz\Db;

use PDO;

/**
 * Database Driver Class
 *
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Driver
{
    /** @var PDO  */
    private $conn;

    /**
     * Return tables information.
     *
     * @return array array('table_name1', 'table_name2', ...)
     * @access public
     */
    abstract public function getMetaTables();

    /**
     * Return Columns information.
     *
     * @return array array('col1' => 'type1', 'col2' => 'type2', ...)
     * @access public
     */
    abstract public function getMetaColumns($tableName);

    /**
     * Return primary key information.
     *
     * @return array array('col1' => 'seq1', 'col2' => 'seq2', ...)
     * @access public
     */
    abstract public function getMetaPrimaryKeys($tableName);

    /**
     * Return forign key information.
     *
     * @return array
     * @access public
     */
    abstract public function getMetaForignKeys();

    /**
     * Return the ID of the last inserted row or sequence value.
     */
    abstract public function lastInsertId($name = null);

    /**
     * Setting DSN.
     *
     * @param string $dsn 
     * @param string $mode error mode for PDO
     * @access public
     */
    public function __construct($dsn, $errMode = null){
        try{
            $this->conn = new PDO($dsn);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e){
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Close connection.
     *
     * @access public
     */
    public function __destruct(){
        $this->conn = null;
    }

    /**
     * Query.
     *
     * @param string $sql
     * @param mixed $p place holder
     * @return PDOStatement
     * @access public
     */
    public function query($sql, $p = null){
        if ($p !== null){
            static $oldSql;
            static $stmt;
            if ($oldSql !== $sql || !$stmt){
                // for speedup
                $oldSql = $sql;
                $stmt = $this->conn->prepare($sql);

                if (!$stmt)
                    return false;
            }

            if (!is_array($p)){
                $p = array($p);
            }

            foreach($p as $k => $v){
                switch (true){
                case is_bool($v):
                    $typ = PDO::PARAM_BOOL;
                    break;
                case is_int($v):
                    $typ = PDO::PARAM_INT;
                    break;
                case is_null($v):
                    $typ = PDO::PARAM_NULL;
                    break;
                default:
                    $typ = PDO::PARAM_STR;
                }
                $stmt->bindValue($k + 1, $v, $typ);
            }
            $stmt->execute();

        }else{
            $stmt = $this->conn->query($sql);
            if (!$stmt)
                return false;
        }

        if ($stmt->errorCode() != '00000'){
            $errInfo = $stmt->errorInfo();
            trigger_error($sql . '['.$errInfo[0].':'.$errInfo[1].']'.$errInfo[2], E_USER_WARNING);
        }

        return $stmt;
    }

    /**
     * PDO Execute.
     *
     * @param string $sql
     * @param mixed $p
     * @return int
     */
    public function execute($sql, $p = null){
        if ($p === null)
            return $this->conn->exec($sql);

        $stmt = $this->query($sql, $p);
        if (!$stmt)
            return 0;
        
        $code = $stmt->errorCode();
        if ($code !== '00000'){ // Error Code defined by ANSI SQL
            $error = $stmt->errorInfo();

            if (isset($error[2]))
                $msg = $error[2];
            else
                $msg = '';
            
            trigger_error("SQL error [$code]：$msg : arguments : " . var_export($p, true),
                          E_USER_WARNING);
            return 0;
        }

        return $stmt->rowCount();
    }

    /**
     * Begin transaction.
     */
    public function begin(){
        return $this->conn->beginTransaction();
    }

    /**
     * Commit transaction.
     */
    public function commit(){
        return $this->conn->commit();
    }

    /**
     * Abort transaction.
     */
    public function abort(){
        return $this->conn->rollBack();
    }

    /**
     * Lock table.
     *
     * @param string $table
     * @access public
     */
    abstract function lock($table);

    public function quote($str, $type = null){
        return $this->conn->quote($str, $type);
    }

    /**
     * Setting PDO error mode.
     *
     * @param int $mode
     */
    public function setErrorMode($mode){
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, $mode);
    }

    /**
     * PDO::lastInsertId
     */
    public function lastInsertIdRaw($name = null){
        return $this->conn->lastInsertId($name);
    }

    /**
     * @param String $sql
     * @return PDOStatement
     */
    public function prepareRaw($sql){
        return $this->conn->prepare($sql);
    }
}
