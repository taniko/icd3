<?php
namespace Hrgruri\Icd3\Model;

class Recommend extends Model
{
    const DEFAULT_SIZE  = 4;
    const MAX_SIZE      = 10;

    public function getAssetRecommend(string $id, string $db, int $num = null)
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

    private function getAssetsInfo(array $assets, string $type, $num = null) : array
    {
        $result = [];
        $num = $this->correctNumber($num);
        if ($type === 'nishikie') {
            $nishikie = new Nishikie($this->capsule);
            for ($i = 0; $i < $num && $i < count($assets); $i++) {
                try {
                    $result[] = $nishikie->getInfo($assets[$i]);
                } catch (\Exception $e) {

                }
            }
        } elseif ($type === 'books') {
            $book = new Book($this->capsule);
            for ($i = 0; $i < $num && $i < count($assets); $i++) {
                try {
                    $result[] = $book->getInfo($assets[$i]);
                } catch (\Exception $e) {

                }
            }
        }
        return $result;
    }
}
