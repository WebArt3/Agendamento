<?php

require_once '_config.php';
require_once "Database.php";
require_once 'App.php';

// classes
$dir = __DIR__.'/classes/';
$classes = dir($dir);
while ($classe = $classes->read()) {
    $php = substr($classe, -4) === '.php'; 
    if ($php === true) {
        require_once $dir.$classe;
    }
}

require_once 'View.php';
require_once 'Mail.php';

require_once __DIR__."/models/ModelRoot.php";
require_once 'Model.php';

// models
$dir = __DIR__.'/models/';
$models = dir($dir);
while ($model = $models->read()) {
    $php = substr($model, -4) === '.php'; 
    if ($php === true) {
        require_once $dir.$model;
    }
}

require_once __DIR__."/integrations/IntegrationRoot.php";
require_once 'Integration.php';

// integrations
$dir = __DIR__.'/integrations/';
$models = dir($dir);
while ($model = $models->read()) {
    $php = substr($model, -4) === '.php'; 
    if ($php === true) {
        require_once $dir.$model;
    }
}

require_once 'funcoes.php';
require_once 'Validation.php';

require_once __DIR__."/controls/ControlRoot.php";
require_once 'Control.php';

// controls
$dir = __DIR__.'/controls/';
$controls = dir($dir);
while ($control = $controls->read()) {
    $php = substr($control, -4) === '.php'; 
    if ($php === true) {
        require_once $dir.$control;
    }
}


