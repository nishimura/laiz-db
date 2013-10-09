#!/usr/bin/php
<?php

//
// Usage: php migration.php [<version>]
//
//        php migration.php              # migrate current version
//        php migration.php 3            # migrate version to 3
//



interface Migrate
{
    // upgrade to this version
    function upgrade(PDO $pdo);
    // downgrade from this version
    function downgrade(PDO $pdo);
}

function getPdo(){
    $dsn = getenv('PDO_DSN');
    if (!$dsn){
        $dbConfig = parse_ini_file('config/di.ini', true);
        $dsn = $dbConfig['Laiz\Db\Db']['methods.setDsn.dsn'];
    }

    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    return $pdo;
}

function getDbVersion(PDO $pdo){
    $sql = "select count(*) from pg_tables where tablename = 'schema_info'";
    $count = null;
    foreach ($pdo->query($sql) as $row)
        $count = $row[0];
    if ($count === null)
        throw new Exception('BUG');
    if ($count === 0){
        createSchemaInfo($pdo);
    }

    $sql = "select * from schema_info";
    $ret = null;
    foreach ($pdo->query($sql) as $row)
        $ret = $row[0];
    if (!is_int($ret))
        throw new Exception('Version is not integer.');
    return $ret;
}

function createSchemaInfo(PDO $pdo){
    $pdo->exec('create table schema_info(version int not null)');
    $pdo->exec('insert into schema_info values(0)');
}

function getCurrentVersion(){
    $files = glob('migrate/*.php');
    natsort($files);
    if (count($files) === 0)
        return 0;

    $file = array_pop($files);
    return str_replace('migrate/', '', str_replace('.php', '', $file));
}


function showHeader($v, $db){
    echo "Specified Version: $v\n";
    echo "Database  Version: $db\n";
}

function prompt($msg){
    echo "$msg [y/n]: ";
    $line = trim(fgets(STDIN));
    if ($line === 'y' || $line === 'Y')
        return true;
    else if ($line === 'n' || $line === 'N')
        return false;

    return prompt($msg);
}

function runUpgrade($v, $db, $pdo){
    if (!prompt('Run Upgrade'))
        return false;

    for ($i = $db + 1; $i <= $v; $i++){
        require_once "migrate/$i.php";
        $class = "Migrate$i";
        $obj = new $class();
        echo "Run ${class}->upgrade() ...\n";
        $obj->upgrade($pdo);
    }
    return true;
}
function runDowngrade($v, $db, $pdo){
    if (!prompt('Run Donwgrade'))
        return false;
    for ($i = $db; $i > $v; $i--){
        require_once "migrate/$i.php";
        $class = "Migrate$i";
        $obj = new $class();
        echo "Run ${class}->downgrade() ...\n";
        $obj->downgrade($pdo);
    }
    return true;
}

function decorate($str, $color){
    $colors = array('bold'    => 1,
                    'white'   => 37,
                    'red'     => 31,
                    'green'   => 32,
                    'bgred'   => 41,
                    'bggreen' => 42,
                    );
    if (!is_array($color) && !isset($colors[$color]))
        return $str;

    if (is_array($color)){
        $patterns = array();
        foreach ($color as $c)
            $patterns[] = $colors[$c];
        $pattern = implode(';', $patterns);
    }else{
        $pattern = $colors[$color];
    }

    echo "\x1b[" . $pattern . 'm' . $str . "\x1b[m\n";
}

function run($version){
    $pdo = getPdo();
    $dbVersion = getDbVersion($pdo);
    showHeader($version, $dbVersion);
    if ($version === $dbVersion){
        echo "Not Update\n";
        exit;
    }

    if ($version > $dbVersion){
        if (!runUpgrade($version, $dbVersion, $pdo))
            exit;
    }else{
        if (!runDowngrade($version, $dbVersion, $pdo))
            exit;
    }

    $pdo->exec("UPDATE schema_info SET version = $version");
    $pdo->commit();

    $tablesFile = 'cache/tables.ini';
    if (file_exists($tablesFile))
        unlink($tablesFile);

    decorate("Success!!", array('bggreen', 'white'));
}

try {
    $version = isset($argv[1]) ? $argv[1] : getCurrentVersion();
    if (!is_numeric($version))
        throw new Exception("Version $version is not numeric.");

    run((int)$version);
}catch (Exception $e){
    decorate("Error!! " . $e->getMessage(), array('bgred', 'white'));
    throw $e;
}
