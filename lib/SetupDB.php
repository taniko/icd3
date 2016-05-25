<?php
namespace Hrgruri\Icd3;

use Hrgruri\Icd3\Dbh;

class SetupDB
{
    /**
     * ログデータをDBに入れる
     * @param  \stdClass $log
     */
    public function insertLog($log)
    {
        preg_match('/\/(.+?)\/(books|mbooks|nishikie)\/results-(big\.php|detail\.php)/',
            $log->request_path,
            $matchd
        );
        try {
            $db_id      = $this->getDbByName($matchd[2]);
            $user_id    = $this->getArcUser($log);
            $asset_id   = $this->getAsset($db_id, $log->request_query_array->f1);
            $dbh = Dbh::get();
            $sth = $dbh->prepare('INSERT INTO arc_log(user_id, asset_id, date, time)
                VALUES (:ui, :ai, :date, :time)'
            );
            $sth->bindParam(':ui', $user_id, \PDO::PARAM_INT);
            $sth->bindParam(':ai', $asset_id, \PDO::PARAM_INT);
            $sth->bindParam(':date', $log->date);
            $sth->bindParam(':time', $log->time);
            $sth->execute();
        } catch (\Exception $e) {
            print $e->getMessage()."\n";
        }
    }

    /**
     * DBの名前からIDを取得する. DBに入っていなければ, 入れてそのIDを取得
     * @param  string $name
     * @return int id
     */
    public function getDbByName(string $name)
    {
        $dbh = Dbh::get();
        $sth = $dbh->prepare('SELECT id FROM db WHERE name = :name');
        $sth->bindParam(':name', $name, \PDO::PARAM_STR);
        $sth->execute();
        if ($sth->rowCount() <= 0) {
            $sth = $dbh->prepare('INSERT INTO db(name) VALUES (:name)');
            $sth->bindParam(':name', $name, \PDO::PARAM_STR);
            $sth->execute();
            $id  = $dbh->lastInsertId();
        } else {
            $data = $sth->fetch();
            $id = $data['id'];
        }
        if ($id == 6) {
            exit();
        }
        return (int)$id;
    }

    /**
     * @param int    $db
     * @param string $name
     * @return int asset id
     */
    public function getAsset(int $db, string $name)
    {
        $dbh = Dbh::get();
        $sth = $dbh->prepare('SELECT asset.id FROM asset
            WHERE asset.name = :name
            AND asset.db = :db'
        );
        $sth->bindParam(':name', $name, \PDO::PARAM_STR);
        $sth->bindParam(':db', $db, \PDO::PARAM_INT);
        $sth->execute();
        if ($sth->rowCount() <= 0) {
            $sth = $dbh->prepare('INSERT INTO asset(db, name) VALUES (:db, :name)');
            $sth->bindParam(':db', $db, \PDO::PARAM_INT);
            $sth->bindParam(':name', $name, \PDO::PARAM_STR);
            $sth->execute();
            $id = $dbh->lastInsertId();
        } else {
            $id = $sth->fetch()['id'];
        }
        return (int)$id;
    }

    private function getArcUser(\stdClass $log)
    {
        $dbh = Dbh::get();
        $sth = $dbh->prepare('SELECT id FROM arc_user
            WHERE ip = :ip
            AND user_agent = :ua
            AND date = :date
        ');
        $sth->bindParam(':ip', $log->ip, \PDO::PARAM_STR);
        $sth->bindParam(':ua', $log->user_agent, \PDO::PARAM_STR);
        $sth->bindParam(':date', $log->date);
        $sth->execute();
        if ($sth->rowCount() <= 0) {
            $sth = $dbh->prepare('INSERT INTO arc_user(ip, user_agent, date) VALUES (:ip, :ua, :date)');
            $sth->bindParam(':ip', $log->ip, \PDO::PARAM_STR);
            $sth->bindParam(':ua', $log->user_agent, \PDO::PARAM_STR);
            $sth->bindParam(':date', $log->date);
            $sth->execute();
            $id = $dbh->lastInsertId();
        } else {
            $id = $sth->fetch()['id'];
        }
        return (int)$id;
    }

    public function insertPoints($parent, $child, $points)
    {
        $dbh = Dbh::get();
        $sth = $dbh->prepare('INSERT INTO recommend(parent, child, points)
            VALUES(:p, :c, :points)
        ');
        $sth->bindParam(':p', $parent, \PDO::PARAM_INT);
        $sth->bindParam(':c', $child, \PDO::PARAM_INT);
        $sth->bindParam(':points', $points);
        $sth->execute();
    }

    public function getCountTable()
    {
        $dbh = DBh::get();
        $sth = $dbh->prepare('select user_id, asset_id, count(*) as num from arc_log group by user_id, asset_id');
        $sth->execute();
        return $sth->fetchAll();
    }
}
