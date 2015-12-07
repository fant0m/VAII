<?php
if (session_id() === ""){
    session_start();
}

require_once 'config/database.php';
require_once 'config/app.php';
require_once 'lib/helpers.php';

spl_autoload_extensions('.php');

spl_autoload_register(function($class) {
    return spl_autoload($class);
});