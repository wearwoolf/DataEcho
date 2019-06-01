<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
include __DIR__ . '/../vendor/autoload.php';

(new \DataEcho\DataEcho())->prepare($_GET)->echo();
