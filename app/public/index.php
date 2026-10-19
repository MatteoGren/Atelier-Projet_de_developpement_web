<?php
declare(strict_types=1);


require_once DIR . '/../vendor/autoload.php';

/* application boostrap */
$appli = require_once DIR . '/../config/bootstrap.php';


$appli->run();