<?php

namespace AKSW\DiscoverShaclShapes\Retrieval;

use AKSW\DiscoverShaclShapes\FileHelper;
use AKSW\DiscoverShaclShapes\ShapeHelper;
use Saft\Rdf\Node;

/**
 * This small file is executed to load RDF files from collected repositories and mark each, which
 * contains RDF about sh:NodeShapes or sh:PropertyShapes.
 */

$rootDir = '/var/www/html/';

require_once $rootDir . 'vendor/autoload.php';

$repositoryFolder = $rootDir . 'docker-data';

$fileHelper = new FileHelper();
$shapeHelper = new ShapeHelper();

$repositoriesWithShapes = array();

foreach ($fileHelper->getFolderList($repositoryFolder) as $folderPath => $folderName) {

    echo PHP_EOL . PHP_EOL . '---------------------- Process '. $folderName ;
    echo PHP_EOL;

    /*
     * get filepaths of all RDF files in that folder
     */
    $ntFiles = $fileHelper->getFilesOfType($folderPath, 'nt');
    $ttlFiles = $fileHelper->getFilesOfType($folderPath, 'ttl');

    /*
     * load each file into the InMemoryStore and check, if it contains Shapes
     */
    // ttl files
    $store = $fileHelper->importFilesToStore($ttlFiles);

    $ttlFileResult = $store->query('
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        PREFIX sh: <http://www.w3.org/ns/shacl#>
        SELECT * WHERE { ?s rdf:type sh:NodeShape. }
    ');

    // get target classes
    $targetClasses = array();
    foreach ($ttlFileResult as $entry) {
        if ($entry['s']->isNamed()) {
            $info = $shapeHelper->getShapeInfo($entry['s']->getUri(), $store);
            if (isset($info['sh:targetClass'])
                && $info['sh:targetClass'] instanceof Node
                && $info['sh:targetClass']->isNamed()) {
                $targetClasses[$info['sh:targetClass']->getUri()] = $info['sh:targetClass']->getUri();
            }
        }
    }

    // greater 0 means, that at least one NodeShape was found
    if (0 < count($ttlFileResult) || 0 < count($ntFileResult)) {
        // add metadata from previous Github call
        $metadataFile = $repositoryFolder . '/' . $folderName . '.json';
        $metadata = json_decode(file_get_contents($metadataFile), true);

        $repositoriesWithShapes[] = array(
            // e.g. https://github.com/AKSW/shacl-shapes
            'repository_url' => 'https://github.com/'. str_replace('___', '/', $folderName),
            'name' => $metadata['full_name'],
            'target_classes' => $targetClasses,
            'description' => $metadata['description']
        );
    }
}

// add the following because processing their files causes problem at the moment
// TODO fix parsing problems with hardf to be able to create this entry like the others
$repositoriesWithShapes[] = array(
    'repository_url' => 'https://github.com/SEMICeu/dcat-ap_shacl',
    'name' => 'SEMICeu/dcat-ap_shacl',
    'target_classes' => array(),
    'description' => 'DCAT-AP SHACL constraint definitions'
);

if (file_exists($rootDir . 'web/repositories.json')) unlink($rootDir . 'web/repositories.json');
file_put_contents($rootDir . 'web/repositories.json', json_encode($repositoriesWithShapes));

echo PHP_EOL;
