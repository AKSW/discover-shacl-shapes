<?php

namespace AKSW\DiscoverShaclShapes;

use Knorke\ResourceGuy;
use Knorke\ResourceGuyHelper;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Store\Store;

/**
 *
 */
class ShapeHelper
{
    /**
     * @param string $shapeUri
     * @param Store $store
     * @return ResourceGuy
     */
    public function getShapeInfo(string $shapeUri, Store $store) : ResourceGuy
    {
        $commonNamespaces = new CommonNamespaces();
        $rdfHelpers = new RdfHelpers();
        $nodeFactory = new NodeFactoryImpl($rdfHelpers);
        $statementFactory = new StatementFactoryImpl();

        $resourceGuyHelper = new ResourceGuyHelper(
            $store,
            array(),
            $statementFactory,
            $nodeFactory,
            $rdfHelpers,
            $commonNamespaces
        );

        return $resourceGuyHelper->createInstanceByUri($shapeUri);
    }
}
