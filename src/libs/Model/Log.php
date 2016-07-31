<?php
namespace Hrgruri\Icd3\Model;

class Log extends Model
{
    public function insertLog($user, $arc_no, $db)
    {
        $asset = new Asset($this->capsule);
        $asset_id = $asset->getAssetId($arc_no, $db);
        $this->capsule->table('log')
            ->insert([
                'user_id'   =>  $user,
                'asset_id'  =>  $asset_id,
                'datetime'  =>  date('Y-m-d H:i:s')
            ]);
    }
}
