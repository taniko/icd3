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

        $app->get('/{db}/detail/{id}[/]', function ($request, $response, $args) {
            return \Hrgruri\Icd3\Controller\Detail::show($args, self::$twig);
        });

        /*  nishikieの設定 */
        $app->get('/nishikie[/]', function ($request, $response, $args) {
            return \Hrgruri\Icd3\Controller\Nishikie::showIndex(
                $args,
                self::$twig,
                $request->getQueryParams()
            );
        });

        $app->get('/nishikie/search[/]', function ($request, $response, $args) {
            return \Hrgruri\Icd3\Controller\Nishikie::showSearch(
                $args,
                self::$twig,
                $request->getQueryParams()
            );
        });
    }
}
