<?php
namespace Hrgruri\Icd3\DB;

abstract class DB
{
    protected $dbh;
    public function __construct()
    {
        $config = (\Hrgruri\Icd3\Config::getInstance())->get('db');
        $dsn        = "mysql:dbname={$config->dbname};host=localhost";
        $user       = $config->user;
        $password   = $config->pass;
        try{
            $dbh = new \PDO($dsn, $user, $password);
            $this->dbh = $dbh;
        }catch (\PDOException $e){
            print("Error: {$e->getMessage()}\n\n");
            die();
        }
    }
}
