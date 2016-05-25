<?php
namespace Hrgruri\Icd3\Setup;

class Cal
{
    private $db;
    private $watched_list;
    private $user_maximum;
    private $slack;

    public function __construct()
    {
        $config = (\Hrgruri\Icd3\Config::getInstance())->get('slack');
        $this->db = new \Hrgruri\Icd3\SetupDB;
        $this->slack = new \Hrgruri\Icd3\Slack($config);
    }

    public function run()
    {
        $this->slack->send('start');
        $count_table = $this->db->getCountTable();
        $user_maximum = [];
        $asset_table = [];
        $watched_list = [];
        foreach ($count_table as $row) {
            // $user_maximumの作成
            if (!isset($user_maximum[$row['user_id']])
                || $user_maximum[$row['user_id']] < $row['num']
            ) {
                $user_maximum[$row['user_id']] = $row['num'];
            }

            // $asset_tableの作成
            $asset_table[$row['asset_id']][] = $row['user_id'];

            // $watched_listの作成
            $watched_list[$row['user_id']][$row['asset_id']] = $row['num'];
        }
        $this->slack->send('create array');
        foreach ($asset_table as $parent => $users) {
            $points_list = [];
            // ポイントの計算
            foreach ($users as $user_id) {
                foreach ($watched_list[$user_id] as $child => $num) {
                    if (!isset($points_list[$child])) {
                        $points_list[$child] = 0;
                    }
                    $points_list[$child]+= ($num / $user_maximum[$user_id]);
                }
            }
            unset($points_list[$parent]);
            ksort($points_list);
            // DBに入れる
            foreach ($points_list as $child => $points) {
                if ($point != 0) {
                    $this->db->insertPoints($parent, $child, $points);
                }
            }
            if ($parent % 1000 == 0) {
                $this->slack->send("done: {$parent}");
            }
        }
        $this->slack->send('end');
    }
}
