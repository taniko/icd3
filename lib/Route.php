<?php
namespace Hrgruri\Icd3;

class Route
{
    private static $twig;

    public static function register($app, $path)
    {
        self::$twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($path)
        );

        $app->get('/', function ($request, $response, $args){
            return self::$twig->loadTemplate('index.twig')->render([]);
        });

        $app->get('/nishikie', function ($request, $response, $args) {
            return \Hrgruri\Icd3\Controller\Nishikie::show($args, self::$twig);
        });

        $app->get('/{db}/detail/{id}', function ($request, $response, $args) {
            return \Hrgruri\Icd3\Controller\Detail::show($args, self::$twig);
        });
    }
}
