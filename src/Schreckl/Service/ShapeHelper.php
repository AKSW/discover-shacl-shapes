<?php

namespace Schreckl\Service;

use Knorke\Importer;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\RdfHelpers;

class ShapeHelper
{
    protected $importer;
    protected $nodeFactory;
    protected $rdfHelpers;

    public function __construct(RdfHelpers $rdfHelpers, NodeFactory $nodeFactory, Importer $importer)
    {
        $this->importer = $importer;
        $this->nodeFactory = $nodeFactory;
        $this->rdfHelpers = $rdfHelpers;
    }

    public function add(string $targetGraphUri, array $data)
    {
        /*
         * Check parameter
         */
        // check title
        if (5 > strlen($data['dc11:title'])) {
            throw new \Exception('Title is too short, needs at least 5 characters.');
        }
        // check description
        if (5 > strlen($data['dc11:description'])) {
            throw new \Exception('Description is too short, needs at least 5 characters.');
        }
        // check files
        $shaclFiles = explode(',', $data['srekl:shacl-file']);
        if (0 == count($shaclFiles)) {
            throw new \Exception('No links found. Please paste at least one link to a SHACL file.');
        } else {
            $shaclFilesAdapted = array();
            foreach ($shaclFiles as $entry) {
                if (false === $this->rdfHelpers->simpleCheckUri($entry)) {
                    throw new \Exception('SHACL files list contains invalid entry (not an URI): '. $entry);
                } else {
                    $shaclFilesAdapted[] = '<'. $entry .'>';
                }
            }
        }

        // check creator
        if ('' !== $data['dc11:creator'] && $this->rdfHelpers->simpleCheckUri($data['dc11:creator'])) {
            $data['dc11:creator'] = '<'. $data['dc11:creator'] .'>';
        } else {
            throw new \Exception('Creator field value has to be an email address or URL. Or leave it empty.');
        }

        /*
         * generate unique URI
         */
        $titlePartOfUri = preg_replace('/[^a-z0-9A-Z-]/', '_', substr($data['dc11:title'], 0, 100));
        $shortHash = substr(hash('sha256', rand(0, 1000) . time()), 0, 6);
        $metadataUri = $targetGraphUri . 'metadata-' . $shortHash . '/' . $titlePartOfUri;

        $this->importer->importString('
            @prefix dc11:  <http://purl.org/dc/elements/1.1/> .
            @prefix rdf:   <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
            @prefix srekl: <https://raw.githubusercontent.com/schreckl/rules/master/schreckl.ttl#> .

            <'. $metadataUri .'>
                rdf:type srekl:ShapeInfo ;
                dc11:title "'. $data['dc11:title'] .'" ;
                dc11:description "'. $data['dc11:description'] .'" ;
                dc11:creator '. $data['dc11:creator'] .' ;
                srekl:shacl-file '. implode(', ', $shaclFilesAdapted) .' ;
                srekl:active "false" .
            ',
            $this->nodeFactory->createNamedNode($targetGraphUri),
            'turtle'
        );
    }
}
