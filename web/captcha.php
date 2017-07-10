<?php

/*
 * provides a captcha image and stores code in session.
 */

session_start();

require __DIR__ .'/../vendor/autoload.php';

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

header('Content-type: image/jpeg');

// Will build phrases of 7 characters
$phraseBuilder = new PhraseBuilder(7);

$builder = new CaptchaBuilder(null, $phraseBuilder);
$builder->setBackgroundColor(255, 255, 255)
    ->setIgnoreAllEffects(true)
    ->build(200, 100)
    ->output();

$_SESSION['captcha'] = $builder->getPhrase();
