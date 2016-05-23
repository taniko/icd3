<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\NishikieClient as Client;

class Nishikie extends \Hrgruri\Icd3\DB
{
    const COUNT = 4;
    const COUNT_LIMIT = 24;

    public static function search($param)
    {
        $param = self::correctParam($param);
        return (new Client())->search($param);
    }

    private static function correctParam($param)
    {
        $param['count'] = (int)($param['count'] ?? 0);
        $param['count'] = $param['count'] > self::COUNT_LIMIT ? self::COUNT_LIMIT : $param['count'];
        $param['count'] = $param['count'] > 0 ? $param['count'] : self::COUNT;
        return $param;
    }

    public static function getDetail($id)
    {
        return (new Client())->getDetail($id);
    }

    public function insertAssets(array $assets)
    {
        $db = 'nishikie';
        $sth = $this->dbh->prepare('SELECT id FROM db WHERE name = :db');
        $sth->bindParam(':db', $db, \PDO::PARAM_INT);
        $sth->execute();
        $db = ($sth->fetch())['id'];
        $sth_in = $this->dbh->prepare('SELECT * FROM asset
            WHERE name = :name
            AND db = :db'
        );
        $sth_insert = $this->dbh->prepare('INSERT INTO asset(db, name) VALUES (:db, :name)');
        foreach ($assets as $asset) {
            $sth_in->bindParam(':name', $asset->id, \PDO::PARAM_STR);
            $sth_in->bindParam(':db', $db, \PDO::PARAM_INT);
            $sth_in->execute();
            if ($sth_in->rowCount() != 1) {
                $sth_insert->bindParam(':name', $asset->id, \PDO::PARAM_STR);
                $sth_insert->bindParam(':db', $db, \PDO::PARAM_INT);
                $sth_insert->execute();
            }
        }
    }
}
