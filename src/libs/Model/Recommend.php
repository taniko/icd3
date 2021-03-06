<?php
namespace Hrgruri\Icd3\Model;

class Recommend extends Model
{
    const DEFAULT_SIZE  = 4;
    const MAX_SIZE      = 10;

    /**
     * 資料から推薦アイテムを取得する
     * @param string $id  ARC管理番号
     * @param string $db  DB名
     * @param int    $num 取得件数
     * @return array
     */
    public function getRecommendByAsset(string $id, string $db, int $num = null) : array
    {
        $result = [];
        $num = $this->correctNumber($num);
        try {
            $asset_id = $this->capsule->table('asset')
                ->select('asset.id')
                ->where('asset.name', '=', $id)
                ->join('db', 'db.id', '=', 'asset.db')
                ->where('db.name', '=', $db)
                ->first()
                ->id;
            $data = $this->capsule->table('asset')
                ->select('asset.name', 'recommend.parent', 'recommend.child', 'recommend.points')
                ->join('db', 'db.id', '=', 'asset.db')
                ->join('recommend', 'recommend.child', '=', 'asset.id')
                ->where('recommend.parent', '=', $asset_id)
                ->where('db.name', '=', $db)
                ->orderBy('recommend.points', 'desc')
                ->get();
            $name_list = [];
            foreach ($data as $key => $val) {
                $name_list[] = $val->name;
            }
            $result = $this->getAssetsInfo($name_list, $db, $num);
        } catch (\Exception $e) {
            // print $e->getMessage();
        }
        return $result;
    }

    /**
     * ユーザIDから推薦アイテムを取得する
     * @param  int    $user ユーザID
     * @param  string $db   DB名
     * @param  int    $num  取得件数
     * @return array
     */
    public function getRecommendByUser(int $user, string $db, int $num = null) : array
    {
        $client = null;
        $result = [];
        $num    = $this->correctNumber($num);
        try {
            if ($db === 'nishikie') {
                $client = new Nishikie($this->capsule, $this->logger);
            } elseif ($db === 'books') {
                $client = new Book($this->capsule, $this->logger);
            } else {
                throw new \Exception();
            }
            /*  ユーザが見たことがあるアイテムを求める */
            $user_items = [];
            $data = $this->capsule->table('log')
                ->select('log.asset_id')
                ->where('log.user_id', '=', $user)
                ->join('asset', 'log.asset_id', '=', 'asset.id')
                ->join('db', 'asset.db', '=', 'db.id')
                ->where('db.name', '=', $db)
                ->distinct()
                ->get();
            foreach ($data as $key => $value) {
                $user_items[] = $value->asset_id;
            }
            $user_items = array_unique($user_items);
            /*  候補者リストを作成   */
            $data = $this->capsule->table('arc_log')
                ->select('arc_log.user_id', 'asset.name', 'asset.id')
                ->whereIn('arc_log.user_id', function($query) use ($user_items){
                    $query->select('arc_log.user_id')
                        ->from('arc_log')
                        ->whereIn('arc_log.asset_id', $user_items)
                        ->orderBy('user_id', 'asc')
                        ->distinct();
                })
                ->join('asset', 'arc_log.asset_id', '=', 'asset.id')
                ->join('db', 'asset.db', '=', 'db.id')
                ->where('db.name', '=', $db)
                ->distinct()
                ->get();
            $candidates = [];
            foreach ($data as $key => $value) {
                $candidates[$value->user_id][] = $value->id;
                $asset_list[$value->user_id][$value->id] = $value->name;
            }
            /*  似たユーザを計算    */
            $used_user  = [];
            $count      = 0;
            $items      = [];
            for ($i = 0; $i < $num && count($used_user) < count($candidates);){
                $recommend_user = null;
                $min_point      = 0;
                foreach ($candidates as $candidate_id => $items) {
                    if (in_array($candidate_id, $used_user)) {
                        continue;
                    } elseif (count(array_diff($items, $user_items)) === 0) {
                        $used_user[] = $candidate_id;
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
                if (is_null($recommend_user)) {
                    continue;
                }
                $tmp_list = array_diff($candidates[$recommend_user], $items, $user_items);
                foreach ($tmp_list as $item_id) {
                    try {
                        $result[] = $client->getInfo($asset_list[$recommend_user][$item_id]);
                        $i++;
                    } catch (\Exception $e) {
                        // error
                        // print $e->getMessage();
                    }
                    if ($i >= $num) {
                        break;
                    }
                }
                $used_user[] = $recommend_user;
            }
        } catch (\Exception $e) {
            //error
            $this->logger->addNotice($e->getMessage().' at getRecommendByUser');
        }
        return $result;
    }

    /**
     * ユーザが見ていないアイテムを人気度順に取得する
     * @param  int    $id  ユーザID
     * @param  string $db  DB名
     * @param  int    $num 取得件数
     * @return array | null
     */
    public function getRecommendByPopular(int $id, string $db, int $num = null)
    {
        $num = $this->correctNumber($num);
        $result = [];
        try {
            if ($db !== 'nishikie' && $db !== 'books') {
                throw new \Exception("unknown db({$db})");
            }
            $db_id = $this->getDbId($db);
            $ignore_assets = (new IgnoreAsset($this->capsule))->getIgnoreList($id);
            $data = $this->capsule->table('arc_log')
                ->select(
                    'arc_log.asset_id',
                    'asset.name',
                    $this->capsule::raw('COUNT(arc_log.asset_id) as num')
                )->where('asset.db', '=', $db_id)
                ->join('asset', 'arc_log.asset_id', '=', 'asset.id')
                ->whereNotIn('asset.id', $ignore_assets)
                ->groupBy('arc_log.asset_id')
                ->orderBy('num', 'DESC')
                ->get();
            $asset_names = [];
            foreach ($data as $key => $value) {
                $asset_names[] = $value->name;
            }
            $result = $this->getAssetsInfo($asset_names, $db, $num);
        } catch (\Exception $e) {
            // print $e->getMessage();
            $result = null;
        }
        return $result;
    }

    public function getRecommendByDate($date, $id, $db, $num = null)
    {
        $num = $this->correctNumber($num);
        $result = null;
        try {
            if ($db !== 'nishikie' && $db !== 'books') {
                throw new \Exception("unknown db({$db})");
            }
            $ia     = new IgnoreAsset($this->capsule);
            $db_id  = $this->getDbId($db);
            $date   = $ia->correctDateMD($date);
            if (is_null($date)) {
                throw new \Exception('date error');
            }
            $result = [];
            $db_id = $this->getDbId($db);
            $ignore_assets = $ia->getIgnoreListByYMD($id, $date);
            $data = $this->capsule->table('arc_log')
                ->select('asset.name', $this->capsule::raw('COUNT(arc_log.asset_id) as num'))
                ->join('asset', 'arc_log.asset_id', '=', 'asset.id')
                ->whereRaw($this->capsule::raw("DATE_FORMAT(date, '%m-%d') = '{$date}'"))
                ->whereNotIn('asset.id', $ignore_assets)
                ->where('asset.db', '=', $db_id)
                ->groupBy('arc_log.asset_id')
                ->orderBy('num', 'DESC')
                ->get();
            $asset_names = [];
            foreach ($data as $key => $value) {
                $asset_names[] = $value->name;
            }
            $result = $this->getAssetsInfo($asset_names, $db, $num);
        } catch (\Exception $e) {
            $result = null;
        }
        return $result;
    }

    /**
     * 取得件数の修正
     * @param  int $num 取得件数
     * @return int      修正後の取得件数
     */
    private function correctNumber(int $num = null) : int
    {
        if (!isset($num)) {
            $num = self::DEFAULT_SIZE;
        } elseif ($num <= 0) {
            $num = 1;
        } elseif ($num > self::MAX_SIZE) {
            $num = self::MAX_SIZE;
        }
        return $num;
    }

    /**
     * ARC管理番号から資料情報を取得する
     * @param  array  $assets
     * @param  string $type
     * @param  [type] $num
     * @return array
     */
    private function getAssetsInfo(array $assets, string $type, $num = null) : array
    {
        $result = [];
        $num = $this->correctNumber($num);
        if ($type === 'nishikie') {
            $nishikie = new Nishikie($this->capsule);
            $i = 0;
            foreach ($assets as $key => $value) {
                try {
                    $result[] = $nishikie->getInfo($value);
                    $i++;
                    if ($i >= $num) {
                        break;
                    }
                } catch (\Exception $e) {}
            }
        } elseif ($type === 'books') {
            $book = new Book($this->capsule);
            $i = 0;
            foreach ($assets as $key => $value) {
                try {
                    $result[] = $book->getInfo($value);
                    $i++;
                    if ($i >= $num) {
                        break;
                    }
                } catch (\Exception $e) {}
            }
        }
        return $result;
    }

    private function getDbId(string $name)
    {
        $result = null;
        $data   = $this->capsule->table('db')
            ->where('name', '=', $name)
            ->get();
        if (count($data) > 0) {
            $result = reset($data)->id;
        }
        return $result;
    }
}
