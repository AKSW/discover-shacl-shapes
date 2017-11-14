<?php

namespace AKSW\DiscoverShaclShapes;

use Knorke\ParserFactory;
use Knorke\Importer;
use Knorke\InMemoryStore;
use Saft\Addition\ARC2\Store\ARC2;
use Saft\Rdf\CommonNamespaces;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\RdfHelpers;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Store\Store;

/**
 * Provides helper functions for file operations.
 */
class FileHelper
{
    /**
     * Returns a list of all files of a given type
     *
     * @param string $folder
     * @param string $fileExt Optional, default is ttl
     * @return array
     */
    public function getFilesOfType(string $folder, string $fileExt = 'ttl')
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder));

        $files = array();

        foreach ($rii as $file) {
            if (!$file->isDir() && $fileExt == pathinfo($file->getPathname())['extension']){
                $files[$file->getPathName()] = $file->getFilename();
            }
        }

        return $files;
    }

    /**
     * Returns a list of all folders of a given folder.
     *
     * @param string $path Full path to root folder
     * @return array
     */
    public function getFolderList(string $path) : array
    {
        $fileSystemIterator = new \FilesystemIterator($path);
        $entries = array();
        foreach ($fileSystemIterator as $entry){
            if ($entry->isDir()) {
                $entries[$entry->getRealPath()] = $entry->getFilename();
            }
        }
        return $entries;
    }

    /**
     * Imports files to InMemoryStore
     *
     * @param array $filesToImport Files to import
     * @return Store
     */
    public function importFilesToStore(array $filesToImport) : Store
    {
        $commonNamespaces = new CommonNamespaces();
        $rdfHelpers = new RdfHelpers();
        $nodeFactory = new NodeFactoryImpl($rdfHelpers);
        $statementFactory = new StatementFactoryImpl();
        $statementIteratorFactory = new StatementIteratorFactoryImpl();

        $store = new InMemoryStore(
            $commonNamespaces,
            $rdfHelpers,
            $nodeFactory
        );

        $importer = new Importer(
            $store,
            new ParserFactory(
                $nodeFactory,
                $statementFactory,
                $statementIteratorFactory,
                $rdfHelpers
            ),
            $nodeFactory,
            $statementFactory,
            $rdfHelpers,
            $commonNamespaces
        );

        // import content of all listed files
        foreach ($filesToImport as $filepath => $filename) {
            try {
                $importer->importFile($filepath, $nodeFactory->createNamedNode('http://to-be-ignored/'), 100);
            } catch (\Exception $e) {
                echo PHP_EOL . 'ERROR with ' . $filepath . ' ==> '. $e->getMessage() . PHP_EOL;
            }
        }

        return $store;
    }
}
