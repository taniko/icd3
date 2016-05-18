<?php
namespace Hrgruri\Icd3\Model;

use Hrgruri\Rarcs\NishikieClient as Client;

class Nishikie
{
    const COUNT = 4;
    const COUNT_LIMIT = 24;

    public static function search($param)
    {
        $param = self::correctParam($param);
        return (new Client())->search($param);
    }

    private static function correctParam($param)
    {
        $param['count'] = (int)($param['count'] ?? 0);
        $param['count'] = $param['count'] > self::COUNT_LIMIT ? self::COUNT_LIMIT : $param['count'];
        $param['count'] = $param['count'] > 0 ? $param['count'] : self::COUNT;
        return $param;
    }

    public static function getDetail($id)
    {
        return (new Client())->getDetail($id);
    }
}
