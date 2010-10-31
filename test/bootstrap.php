<?php
require_once __DIR__ . '/../lib/vendor/Autoload/lib/StasisMedia/Autoload/Autoload.php';

use StasisMedia\Autoload\Autoload;

Autoload::register();
Autoload::addPath(__DIR__ . '/../lib');
Autoload::addPath(__DIR__ . '/lib');