<?php
namespace Hrgruri\Icd3\Model;

class Recommend extends \Hrgruri\Icd3\DB\DB
{
    public function getByDetail($db, $id, $limit = 4)
    {
        $limit = is_int($limit) && $limit > 0 ? $limit : 4;
        $res = [];
        $sth = $this->dbh->prepare('SELECT asset.id, asset.name FROM recommend, asset
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

    public function getUserRecommend($db, $id, $token, $limit = 4)
    {
        return [];
    }
}
