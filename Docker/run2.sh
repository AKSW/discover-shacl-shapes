#!/bin/bash

composer update

apache2-foreground

tail -f /dev/null
