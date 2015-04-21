<?php

if (class_exists("WPFW\WPFW")) {
    die("WPFW already loaded.");
}

define("WPFW_DIR", __DIR__ . "/WPFW");

require(WPFW_DIR . "/WPFW.php");

$wpfw = new WPFW\WPFW();
