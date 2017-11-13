<?php

/**
 * This small file is executed to clone all loaded SHACL repositories (/tmp/shacl-repos.json) locally.
 */

$repositoriesFile = '/tmp/shacl-repos.json';
$repositoriesList = json_decode(file_get_contents($repositoriesFile), true);

$rootDir = __DIR__ .'/../../../';

foreach ($repositoriesList['items'] as $repository) {

    echo PHP_EOL . PHP_EOL . 'Repository: https://github.com/'. $repository['full_name'] . PHP_EOL;

    $normalizedRepositoryName = str_replace('/', '_', $repository['full_name']);

    // if repository already exists, update it
    if (file_exists($rootDir .'/docker-data/'. $normalizedRepositoryName)) {
        echo exec(
            'cd '. $rootDir .'/docker-data/'. $normalizedRepositoryName
            . ' && git pull'
        );

    // otherwise clone
    } else {
        echo exec(
            'cd '. $rootDir .'/docker-data/'
            . '&& git clone https://github.com/'. $repository['full_name'] .' '. $normalizedRepositoryName
            .' && cd '. $rootDir
        );
    }
}

echo PHP_EOL;
