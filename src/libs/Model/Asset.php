<?php
namespace Hrgruri\Icd3\Model;

class Asset extends Model
{
    public function getAssetId($arc_no, $db)
    {
        $result = null;
        $db_id = $this->getDbId($db);
        $data = $this->capsule->table('asset')
            ->where('db', '=', $db_id)
            ->where('name', '=', $arc_no)
            ->get();
        if (count($data) > 0) {
            $result = reset($data)->id;
        } else {
            $data = $this->capsule->table('asset')
                ->InsertGetId([
                    'db'    => $db_id,
                    'name'  => $arc_no
                ]);
            $result = $data;
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
