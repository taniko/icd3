<?php
namespace Hrgruri\Icd3\Controller;

use Hrgruri\Icd3\Model\Session;
use Hrgruri\Icd3\Model\Books as M;
use Hrgruri\Icd3\Model\Recommend;

class Books
{
    const NAME = 'books';

    public static function showIndex($args, $twig, $param)
    {
        $session = self::setSession();
        $assets = Recommend::getByUser(self::NAME, $session['id']);
        if (count($assets) <= 0) {
            $assets = Recommend::getByPopular(self::NAME, $session['id']);
        }
        $html = ($twig->loadTemplate('books.twig'))->render([
            'title'     => '古典データベース',
            'assets'    => $assets,
            'db'        => self::NAME,
        ]);
        return $html;
    }

    public static function showSearch($args, $twig, $param)
    {
        self::setSession();
        $assets = M::search($param);
        $html = ($twig->loadTemplate('books.twig'))->render([
            'title'     => '古典データベース',
            'assets'    => $assets,
            'db'        => self::NAME,
            'next_link' => M::getNextLink($param),
            'prev_link' => M::getPrevLink($param)
        ]);
        return $html;
    }

    private static function setSession()
    {
        $session = Session::get();
        $_SESSION['id']     = $session['id'];
        $_SESSION['token']  = $session['token'];
        return $session;
    }
}