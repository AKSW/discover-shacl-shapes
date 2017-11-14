<?php

use AKSW\DiscoverShaclShapes\Service\TwigExtension\AssetExtension;
use AKSW\DiscoverShaclShapes\Service\TwigExtension\UrlExtension;
use Saft\Rdf\CommonNamespaces;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app['debug'] = true;

$commonNamespaces = new CommonNamespaces();

/*
 * init twig template engine
 */
$app->register(new TwigServiceProvider(), array('twig.path' => __DIR__ .'/views'));
$app['twig'] = $app->extend('twig', function ($twig, $app) use ($config, $commonNamespaces) {
    // add extensions
    $twig->addExtension(new AssetExtension($config['url'], 'assets/'));
    $twig->addExtension(new UrlExtension($commonNamespaces));

    return $twig;
});

$request = Request::createFromGlobals();

/*
 * routes
 */

// startpage
$app->get('/', function() use ($app, $store, $config, $dataBlankHelper, $request) {
    // cut of first 50 signs of the query and ignore the rest
    $searchQuery = substr($request->query->get('search_query'), 0, 50);

    if (!empty($searchQuery)) {
        $repos = json_decode(file_get_contents(__DIR__ .'/../web/repositories.json'), true);
        $reposToDisplay = array();

        foreach ($repos as $key => $repo) {
            if (false !== strpos($repo['name'], $searchQuery)) {
                $reposToDisplay[] = $repo;
            } elseif (false !== strpos($repo['description'], $searchQuery)) {
                $reposToDisplay[] = $repo;
            }

            // check target class URIS of found shapes
            foreach ($repo['target_classes'] as $class) {
                if (false !== strpos(strtolower($class), strtolower($searchQuery))) {
                    $reposToDisplay[] = $repo;
                    break;
                }
            }
        }
    } else {
        $reposToDisplay = json_decode(file_get_contents(__DIR__ .'/../web/repositories.json'), true);
    }

    return $app['twig']->render('index.html.twig', array(
        'search_query' => $searchQuery,
        'repositories' => $reposToDisplay,
        'url' => $config['url']
    ));
});

// about
$app->get('/about', function() use ($app, $config) {
    return $app['twig']->render('about.html.twig', array(
        'url' => $config['url']
    ));
});

return $app;
