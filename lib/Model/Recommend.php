<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\NishikieClient as Client;
use Hrgruri\Icd3\Dbh;

class Recommend
{
    const LIMIT = 4;

    public static function getByDetail($db, $id, $limit = 0)
    {
        $limit = is_int($limit) && $limit > 0 ? $limit : self::LIMIT;
        $res = [];
        $dbh = Dbh::get();
        $sth = $dbh->prepare('SELECT asset.id, asset.name FROM recommend, asset
            WHERE recommend.parent =
                (SELECT id FROM asset
                    WHERE name = :id
                    AND db = (SELECT id FROM db WHERE name = :db)
                )
            AND recommend.child = asset.id
            ORDER BY points DESC'
        );
        $sth->bindParam(':id', $id, \PDO::PARAM_STR);
        $sth->bindParam(':db', $db, \PDO::PARAM_STR);
        $sth->execute();
        if ($db == 'nishikie') {
            $client = new \Hrgruri\Rarcs\NishikieClient();
            for ($i = 0; $i < $limit && ($data = $sth->fetch()); $i++) {
                try {
                    $res[] = $client->getDetail($data['name']);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return $res;
    }

    public static function getByUser($db, $id, int $limit = null)
    {
        $limit = is_int($limit) && $limit > 0 ? $limit : self::LIMIT;
        $assets = [];
        $dbh = Dbh::get();
        return $assets;
    }
}
