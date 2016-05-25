<?php
namespace Hrgruri\Icd3\Setup;

class Init
{
    private $dir;

    public function __construct($dir)
    {
        $this->dir = rtrim($dir, '/');
    }

    /**
     * 必要なログを選んでDBに入れる
     */
    public function insertLog()
    {
        $db = new \Hrgruri\Icd3\SetupDB;
        for($i = 1; $i < 60; $i++) {
            $data = json_decode(file_get_contents("{$this->dir}/log_{$i}.json"));
            print "{$i} =====\n";
            $j = 0;
            foreach ($data as $log) {
                if(
                    $log->request_method === 'GET' &&
                    $log->http_status === '200' &&
                    preg_match('/\/(.+?)\/(books|mbooks|nishikie)\/results-(big\.php|detail\.php)/', $log->request_path, $matchd) == 1 &&
                    isset($log->request_query_array->f1)
                ) {
                    $db->insertLog($log);
                }
                $j++;
                print "{$j} / ".count($data)."\r";
            }
            unset($data);
            print "\n\n";
        }
    }
}
