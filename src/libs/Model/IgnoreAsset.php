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
}
