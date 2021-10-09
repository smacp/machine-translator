<?php

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$envFile = new SplFileObject('.env.test');

while (!$envFile->eof()) {
    $var = trim($envFile->fgets());
    if ($var && strpos($var, '#') !== 0) {
        putenv($var);
    }
}
