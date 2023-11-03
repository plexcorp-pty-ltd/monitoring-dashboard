<?php

if (!defined('STDIN') && PHP_SAPI !== 'cli' && !empty($_SERVER['REMOTE_ADDR'])) {
    throw new \Exception("Script not running via a terminal session.");
}

require_once "./vendor/autoload.php";

use Dotenv\Dotenv;

if(file_exists(".env")) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();    
}

$allowed = [
    'Agent',
    'MakeUser',
    'Install',
];

$task = $argv[1] ?? null;

if ($task === null) {
    throw new \Exception("No task specified.");
}

if (!in_array($task, $allowed)) {
    throw new \Exception("Console task is not registered.");
}

$klass = "\\Plexcorp\\Monitoring\\Console\\" . $task;
$console = new $klass($argv);
$console->run();