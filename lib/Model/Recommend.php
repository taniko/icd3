<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\NishikieClient;
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
            $dbh = Dbh::get();

            // DBのIDを求める
            $sth = $dbh->prepare('SELECT db.id FROM db
                WHERE db.name = :db'
            );
            $sth->bindParam(':db', $db, \PDO::PARAM_STR);
            $sth->execute();
            $db_id = $sth->fetch()['id'];

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
                $client = new NishikieClient();
                $i      = 0;
                while (($data = $sth->fetch()) && $i < self::LIMIT) {
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
}
