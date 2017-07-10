<?php

/**
 * This files contains small helpers to boost usage.
 */

use Knorke\Data\ParserFactory;
use Knorke\Importer;
use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Result\ResultFactoryImpl;

/**
 * Deletes a folder (recursively).
 *
 * @param string $path
 */
function deleteDir(string $path)
{
    if (empty($path)) {
        return false;
    }
    return is_file($path) ?
            @unlink($path) :
            array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

/**
 * Returns a list of all files of a given type
 *
 * @param string $folder
 * @param string $fileExt Optional, default is .ttl
 * @return array
 */
function getFilesOfType(string $folder, string $fileExt = '.ttl')
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));

    $files = array();

    foreach ($rii as $file) {
        if (!$file->isDir() && 'ttl' == pathinfo($file->getPathname())['extension']){
            $files[$file->getPathName()] = $file->getFilename();
        }
    }

    return $files;
}

/**
 * @param array $filesToImport Files to import
 * @param array $dbCredentials Credentials for store.
 * @param string $targetGraph
 */
function importFiles(array $filesToImport, array $dbCredentials, string $targetGraph)
{
    /*
     * store related
     */
    $commonNamespaces = new CommonNamespaces();
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
        $dbCredentials
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

    // clean store
    $store->emptyAllTables();

    echo PHP_EOL . 'Start importing:';

    // import content of all listed files
    foreach ($filesToImport as $filepath => $filename) {
        echo PHP_EOL . '- read in '. $filepath .' and import to '. $targetGraph;
        $importer->importFile($filepath, $nodeFactory->createNamedNode($targetGraph), 100);
    }
    echo PHP_EOL;
}

/**
 * Unzips a file to a given folder.
 *
 * @param string $filepath File to unzip.
 * @param string $targetDir Folder to unzip files into.
 * @throws Exception If zip archive could not be opened.
 */
function unzipFile($filepath, $targetDir)
{
    @deleteDir($targetDir);
    $zip = new ZipArchive;
    $res = $zip->open($filepath);
    if ($res === TRUE) {
        $zip->extractTo($targetDir);
        $zip->close();
    } else {
        throw new Exception('Zip archive could not be opened: '. $filepath);
    }
}
