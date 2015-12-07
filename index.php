<?php
use lib\Router;

require "bootstrap.php";
require "routes.php";

echo Router::dispatch();