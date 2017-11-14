<?php

/**
 * This small file is executed to clone all loaded SHACL repositories (/tmp/shacl-repos.json) locally.
 */

$repositoriesFile = '/tmp/shacl-repos.json';
$repositoriesList = json_decode(file_get_contents($repositoriesFile), true);

// add known repositories, until they use shacl-shapes as topic too
$repositoriesList['items'][] = array(
    'full_name' => 'w3c/data-shapes',
    'name' => 'data-shapes',
    'owner' => array('login' => 'w3c'),
    'description' => 'RDF Data Shapes WG repo'
);

$rootDir = '/var/www/html/';

foreach ($repositoriesList['items'] as $repository) {

    echo PHP_EOL . PHP_EOL . 'Repository: https://github.com/'. $repository['full_name'] . PHP_EOL;

    // foo/bar => foo_bar
    $normalizedRepositoryName = $repository['owner']['login'] .'___'. $repository['name'];

    // if repository already exists, update it
    if (file_exists($rootDir .'docker-data/'. $normalizedRepositoryName)) {
        echo exec(
            'cd '. $rootDir .'docker-data/'. $normalizedRepositoryName
            . ' && git pull'
        );

    // otherwise clone
    } else {
        echo exec(
            'cd '. $rootDir .'docker-data/'
            . '&& git clone https://github.com/'. $repository['full_name'] .' '. $normalizedRepositoryName
            .' && cd '. $rootDir
        );
    }

    // store metadata for later usage
    file_put_contents($rootDir .'docker-data/'. $normalizedRepositoryName . '.json', json_encode($repository));
}

echo PHP_EOL;
