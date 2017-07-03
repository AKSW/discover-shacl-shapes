<?php

use Knorke\DataBlankHelper;
use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;
use Schreckl\Service\TwigExtension\AssetExtension;
use Schreckl\Service\TwigExtension\UrlExtension;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
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
        $nodeFactory->createNamedNode($config['graphs']['rules'])
    )
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

    if (null !== $searchQuery) {
        foreach ($shapeInfos as $key => $shapeInfo) {
            $found = false;

            if (false !== strpos($shapeInfo['dc11:title'], $searchQuery)) {
                $found = true;
            } elseif (false !== strpos($shapeInfo['dc11:description'], $searchQuery)) {
                $found = true;
            } elseif (false !== strpos($shapeInfo['sh:targetClass']['_idUri'], $searchQuery)) {
                $found = true;
            }

            if (false == $found) {
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

return $app;
