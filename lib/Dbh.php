<?php
namespace Hrgruri\Icd3;

class Dbh
{
    public static function get()
    {
        $config = (\Hrgruri\Icd3\Config::getInstance())->get('db');
        $dsn        = "mysql:dbname={$config->dbname};host=localhost";
        try{
            $dbh = new \PDO($dsn, $config->user, $config->pass);
        }catch (\PDOException $e){
            $dbh = null;
        }
        return $dbh;
    }
}
