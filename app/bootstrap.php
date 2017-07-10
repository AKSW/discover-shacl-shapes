<?php

use Gregwar\Captcha\CaptchaBuilder;
use Knorke\DataBlankHelper;
use Knorke\Importer;
use Knorke\Data\ParserFactory;
use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Schreckl\Service\ShapeHelper;
use Schreckl\Service\TwigExtension\AssetExtension;
use Schreckl\Service\TwigExtension\UrlExtension;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app['debug'] = true;

/*
 * store related
 */
$commonNamespaces = new CommonNamespaces();
$commonNamespaces->add('building', 'https://github.com/AKSW/leds-asp-f-ontologies/raw/master/ontologies/building/ontology.ttl#');
$commonNamespaces->add('sh', 'http://www.w3.org/ns/shacl#');
$commonNamespaces->add('srekl', 'https://raw.githubusercontent.com/schreckl/rules/master/schreckl.ttl#');

$rdfHelpers = new RdfHelpers();
$nodeFactory = new NodeFactoryImpl($rdfHelpers);
$queryFactory = new QueryFactoryImpl($rdfHelpers);
$resultFactory = new ResultFactoryImpl();
$statementFactory = new StatementFactoryImpl();
$statementIteratorFactory = new StatementIteratorFactoryImpl();

$store = new ARC2(
    $nodeFactory,
    $statementFactory,
    $queryFactory,
    $resultFactory,
    $statementIteratorFactory,
    $rdfHelpers,
    $commonNamespaces,
    $config['db']
);

$dataBlankHelper = new DataBlankHelper(
    $commonNamespaces,
    $statementFactory,
    $nodeFactory,
    $rdfHelpers,
    $store,
    array(
        $nodeFactory->createNamedNode($config['graphs']['shapes']),
        $nodeFactory->createNamedNode($config['graphs']['shapes_by_thirdparty']),
    )
);

$parserFactory = new ParserFactory(
    $nodeFactory,
    $statementFactory,
    $statementIteratorFactory,
    $rdfHelpers
);

$importer = new Importer(
    $store,
    $parserFactory,
    $nodeFactory,
    $statementFactory,
    $rdfHelpers,
    $commonNamespaces
);

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
    $searchQuery = $request->query->get('search_query');
    $shapeInfos = $dataBlankHelper->find('srekl:ShapeInfo');

    if (null !== $searchQuery && 1 < strlen($searchQuery)) {
        foreach ($shapeInfos as $key => $shapeInfo) {
            $found = false;

            if (false !== strpos(strtolower($shapeInfo['dc11:title']), strtolower($searchQuery))) {
                $found = true;
            } elseif (false !== strpos(strtolower($shapeInfo['dc11:description']), strtolower($searchQuery))) {
                $found = true;
            } elseif (false !== strpos(strtolower($shapeInfo['dc11:creator']), strtolower($searchQuery))) {
                $found = true;
            }

            if (false == $found
                || (isset($shapeInfo['srekl:active']) && 'true' !== $shapeInfo['srekl:active'])) {
                unset($shapeInfos[$key]);
            }
        }
    // if srekl:active is set, but false (only for thirdparty entries)
    } else {
        foreach ($shapeInfos as $key => $shapeInfo) {
            if (isset($shapeInfo['srekl:active']) && 'true' !== $shapeInfo['srekl:active']) {
                unset($shapeInfos[$key]);
            }
        }
    }

    return $app['twig']->render('index.html.twig', array(
        'search_query' => $searchQuery,
        'shape_infos' => $shapeInfos,
        'url' => $config['url']
    ));
});

// about
$app->get('/about', function() use ($app, $config) {
    return $app['twig']->render('about.html.twig', array(
        'url' => $config['url']
    ));
});

// add additional shapes
$app->match('/register-shapes', function() use ($app, $config, $rdfHelpers, $importer, $nodeFactory, $request) {

    $error = '';
    $created = false;
    session_start();

    // check captcha
    if ('insert' == $request->request->get('action')) {
        if ($_SESSION['captcha'] == $request->request->get('captcha_by_user')
            && '' == $request->request->get('captcha')) { // honeypot has to be empty
            try {
                $shapeHelper = new ShapeHelper($rdfHelpers, $nodeFactory, $importer);
                $shapeHelper->add(
                    $config['graphs']['shapes_by_thirdparty'],
                    $request->request->all()
                );

                // to avoid multiple calls
                $_SESSION['captcha'] = null;
                $created = true;
            } catch(Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'Captcha is wrong.';
        }
    }

    return $app['twig']->render('register-shapes.html.twig', array(
        'captcha_img' => $config['url'] . 'captcha.php',
        'created' => $created,
        'data' => 0 < count($request->request->all()) ? $request->request->all() : array(
            'dc11:title' => '',
            'dc11:description' => '',
            'dc11:creator' => '',
            'srekl:shacl-file' => '',
        ),
        'error' => $error,
        'url' => $config['url']
    ));
})->method('GET|POST');

return $app;
