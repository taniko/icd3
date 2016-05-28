<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\NishikieClient;
use Hrgruri\Rarcs\BooksClient;
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
            $client = new NishikieClient();
            for ($i = 0; $i < $limit && ($data = $sth->fetch()); $i++) {
                try {
                    $res[] = $client->getDetail($data['name']);
                } catch (\Exception $e) {
                    continue;
                }
            }
        } elseif ($db == 'books') {
            $client = new BooksClient();
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

    /**
     * ユーザの推薦アイテムを求める
     * @param  string $db    DBの名前
     * @param  int    $id    ユーザID
     * @param  int $limit 求めるアイテムの最大数
     * @return array
     */
    public static function getByUser(string $db, int $id, int $limit = null)
    {
        $limit = is_int($limit) && $limit > 0 ? $limit : self::LIMIT;
        $assets = [];
        try {
            $db_id = self::getDB($db);

            $dbh = Dbh::get();

            /*  ユーザの見たアイテムを求める */
            $user_items = [];
            $sth = $dbh->prepare('SELECT asset.id FROM asset
                WHERE asset.db = :db
                AND asset.id IN (
                    SELECT asset_id FROM log
                    WHERE log.user_id = :id
                )'
            );
            $sth->bindParam(':db', $db_id, \PDO::PARAM_INT);
            $sth->bindParam(':id', $id, \PDO::PARAM_INT);
            $sth->execute();
            while($data = $sth->fetch()){
                $user_items[] =  $data['id'];
            }

            /*  似たユーザ候補を探す */
            $candidates = [];
            $sth = $dbh->prepare('SELECT DISTINCT arc_log.user_id, arc_log.asset_id, asset.name
                FROM arc_log, asset
                WHERE arc_log.user_id IN (
                    SELECT DISTINCT user_id
                    FROM arc_log
                    WHERE asset_id IN (
                        SELECT DISTINCT log.asset_id
                        FROM log
                        WHERE log.user_id = :id
                    )
                )
                AND arc_log.asset_id = asset.id
                AND asset.db = :db
                ORDER BY arc_log.user_id ASC'
            );
            $sth->bindParam(':id', $id, \PDO::PARAM_INT);
            $sth->bindParam(':db', $db_id, \PDO::PARAM_INT);
            $sth->execute();
            $asset_list = [];
            while ($data = $sth->fetch()) {
                $candidates[$data['user_id']][] = $data['asset_id'];
                $asset_list[$data['asset_id']]  = $data['name'];
            }
            $recommend_user = null;
            $min_point      = 0;
            foreach ($candidates as $candidate_id => $items) {
                // アイテムが完全に一致するものは無視する
                if (count(array_diff($items, $user_items)) === 0) {
                    continue;
                }
                $numerator      = count(array_intersect($user_items, $items));
                $denominator    = count(array_unique(array_merge($user_items, $items)));
                $tmp_point  = $numerator / $denominator;
                if ($min_point < $tmp_point){
                    $recommend_user = $candidate_id;
                    $min_point      = $tmp_point;
                }
            }
            if (!is_null($recommend_user)) {
                $sth = $dbh->prepare('SELECT DISTINCT arc_log.asset_id
                    FROM arc_log
                    WHERE arc_log.user_id = :ru
                    AND arc_log.asset_id NOT IN (
                        SELECT DISTINCT log.asset_id
                        FROM log
                        WHERE log.user_id = :id
                    )'
                );
                $sth->bindParam(':ru', $recommend_user, \PDO::PARAM_INT);
                $sth->bindParam(':id', $id, \PDO::PARAM_INT);
                $sth->execute();
                if ($db == 'nishikie') {
                    $client = new NishikieClient();
                } elseif ($db = 'books') {
                    $client = new BooksClient();
                } else {
                    throw new \Exception();
                }
                $i      = 0;
                while (($data = $sth->fetch()) && $i < $limit) {
                    try {
                        $assets[] = $client->getDetail($asset_list[$data['asset_id']]);
                        $i++;
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }
                }
            }
        } catch (\PDOExecption $e) {
            error_log($e->getMessage());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        return $assets;
    }

    /**
     * 人気度順の推薦アイテムを取得する
     * @param  string $db    DB名
     * @param  int    $id    ユーザID
     * @param  int    $limit 取得件数
     * @return array
     */
    public static function getByPopular(string $db, int $id, int $limit = self::LIMIT)
    {
        $limit  = $limit > 0 ? $limit : self::LIMIT;
        $assets = [];
        try {
            if ($db == 'nishikie') {
                $client = new NishikieClient();
            } elseif ($db == 'books') {
                $client = new BooksClient();
            } else {
                throw new \Exception("対象外のDB : {$db}");
            }
            $db_id = self::getDB($db);
            $dbh = Dbh::get();
            $sth = $dbh->prepare('SELECT arc_log.asset_id, asset.name ,count(arc_log.asset_id) AS num
                FROM asset, arc_log
                WHERE asset.db = :db
                AND asset.id NOT IN (SELECT asset_id FROM log WHERE user_id = :ui)
                AND asset.id = arc_log.asset_id
                GROUP BY (arc_log.asset_id)
                ORDER BY num DESC'
            );
            $sth->bindParam(':db',  $db_id, \PDO::PARAM_INT);
            $sth->bindParam(':ui',  $id, \PDO::PARAM_INT);
            $sth->execute();
            $i = 0;
            while (($data = $sth->fetch()) && $i < $limit) {
                try {
                    $assets[] = $client->getDetail($data['name']);
                    $i++;
                } catch (\InvalidArgumentException $e) {
                    error_log("{$e->getMessage()} :{$db}/{$data['name']}");
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
            }
        } catch (\PDOExecption $e) {
            error_log($e->getMessage());
        } catch (\Execption $e) {
            error_log($e->getMessage());
        }
        return $assets;
    }

    /**
     * DBのIDを取得する
     * @param  string $name DB名
     * @return int DBのID
     */
    private static function getDB(string $name) : int {
        $dbh = Dbh::get();
        $sth = $dbh->prepare('SELECT db.id FROM db
            WHERE db.name = :db'
        );
        $sth->bindParam(':db', $name, \PDO::PARAM_STR);
        $sth->execute();
        return $sth->fetch()['id'];
    }
}
