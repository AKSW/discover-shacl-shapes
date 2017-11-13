#!/bin/bash

# load all Github repositories with topic "shacl"
echo ""
echo ""
echo "####################################################"
echo "Load all Github repositories which have topic: shacl"
echo "####################################################"
echo ""
curl -H "Accept: application/vnd.github.mercy-preview+json" "https://api.github.com/search/repositories?q=topic:shacl" > /tmp/shacl-repos.json

# clone each Github repository locally (/docker-data)
echo ""
echo ""
echo "#################################"
echo "Clone/update repositories locally"
echo "#################################"
php src/Schreckl/Retrieval/CloneGithubRepositories.php

# set file permission to 0777 so that cloned repositories can be removed outside of the container
