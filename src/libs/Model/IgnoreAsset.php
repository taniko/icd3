<?php
namespace Hrgruri\Icd3\Model;

class IgnoreAsset extends Model
{
    /**
     * 無視するアイテムの配列を取得する
     * @param  int    $user ユーザID
     * @return array
     */
    public function getIgnoreList(int $user)
    {
        $result = [];
        $data = $this->capsule->table('ignore_assets')
            ->select('asset_id')
            ->where('user_id', '=', $user)
            ->distinct()
            ->get();
        foreach ($data as $key => $value) {
            $result[] = $value->asset_id;
        }
        return $result;
    }

    public function insertIgnoreAsset($user, $arc_no, $db)
    {
        $asset_id = (new Asset($this->capsule))->getAssetId($arc_no, $db);
        $this->capsule->table('ignore_assets')
            ->insert([
                'user_id'   =>  intval($user),
                'asset_id'  =>  intval($asset_id),
                'date'      =>  date('Y-m-d')
            ]);
    }

    /**
     * 特定の年月日の無視するアイテムの配列を取得する
     * @param  int    $user ユーザID
     * @param  string $date 日付 ('yyyy-mm-dd')
     * @return array
     */
    public function getIgnoreListByYMD(int $user, string $date)
    {
        $result = [];
        $date = $this->correctDateYMD($date);
        $data = $this->capsule->table('ignore_assets')
            ->select('asset_id')
            ->where('user_id', '=', $user)
            ->whereDate('date', '=', $date)
            ->distinct()
            ->get();
        foreach ($data as $key => $value) {
            $result[] = $value->asset_id;
        }
        return $result;
    }

    /**
     * 日付の修正
     * @return string $date yyyy-mm-dd))
     */
    public function correctDateYMD(string $date)
    {
        if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $date) === 1) {
            $tmp    =   explode('-', $date);
            $date   =   $tmp[0];
            $date   .= strlen($tmp[1])>1 ? '-'.$tmp[1] : '-0'.$tmp[1];
            $date   .= strlen($tmp[2])>1 ? '-'.$tmp[2] : '-0'.$tmp[2];
        } elseif (preg_match('/[0-9]{1,2}-[0-9]{1,2}/', $date) === 1) {
            $tmp    =   explode('-', $date);
            $date   = date('Y');
            $date   .= strlen($tmp[0])>1 ? '-'.$tmp[0] : '-0'.$tmp[0];
            $date   .= strlen($tmp[1])>1 ? '-'.$tmp[1] : '-0'.$tmp[1];
        } else {
            $date = null;
        }
        return $date;
    }

    /**
     * 日付を月日のみにする
     * @return string $date ((yyyy-)mm-dd))
     */
    public function correctDateMD(string $date)
    {
        if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $date) === 1) {
            $tmp    =   explode('-', $date);
            $date   =   '';
            $date   .= strlen($tmp[1])>1 ? $tmp[1] : '0'.$tmp[1];
            $date   .= strlen($tmp[2])>1 ? '-'.$tmp[2] : '-0'.$tmp[2];
        } elseif (preg_match('/[0-9]{1,2}-[0-9]{1,2}/', $date) === 1) {
            $tmp    =   explode('-', $date);
            $date   =   '';
            $date   .= strlen($tmp[0])>1 ? $tmp[0] : '0'.$tmp[0];
            $date   .= strlen($tmp[1])>1 ? '-'.$tmp[1] : '-0'.$tmp[1];
        } else {
            $date = null;
        }
        return $date;
    }
}
