<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Icd3\Dbh;
use Hrgruri\Icd3\Model\Session;

class Log
{
    public static function commit(string $db_name, string $arc_no)
    {
        try {
            $dbh = Dbh::get();
            $sth = $dbh->prepare('SELECT asset.id FROM asset,db
                WHERE asset.name = :asset
                AND asset.db = db.id
                AND db.name = :db'
            );
            $sth->bindParam(':asset', $arc_no, \PDO::PARAM_STR);
            $sth->bindParam(':db', $db_name, \PDO::PARAM_STR);
            $sth->execute();
            if ($sth->rowCount() != 1) {
                throw new \Exception();
            }
            $asset_id = $sth->fetch()['id'];
            $sth = $dbh->prepare('INSERT INTO log(user_id, asset_id, datetime)
                VALUES (:ui, :ai, :dt)'
            );
            $session = Session::get();
            $now = date("Y-m-d H:i:s");
            $sth->bindParam(':ui', $session['id'], \PDO::PARAM_INT);
            $sth->bindParam(':ai', $asset_id, \PDO::PARAM_INT);
            $sth->bindParam(':dt', $now, \PDO::PARAM_STR);
            $sth->execute();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
