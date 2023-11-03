<?php session_start();
/**
 * This is the index controller, it takes care of routing requests to the relevant API or controller.
 * We expect two GET arguments, both are optional and if not set - we just render the main dashboard or login screen if not logged in.
 * 
 * Currently there's just one controller that handles most of the stat operations i.e. src/ApiController.
 */
require_once("../vendor/autoload.php");

use Dotenv\Dotenv;
if(file_exists("../.env")) {
    $dotenv = Dotenv::createImmutable("..".__DIR__);
    $dotenv->load();    
}

use Plexcorp\Monitoring\ApiController;
use Plexcorp\Monitoring\StatsModel;


//can be "api", "login"
$action = $_GET['action'] ?? '';

// can be authenticate, savestats
$route = $_GET['route'] ?? '';

// If it's set, means the user is logged in.
$sessionKey = $_SESSION['SESSIONKEY'] ?? '';

// This allows for agents to POST their stats to the API without logging in.
// We use an API key in the .env file SHELL_API_KEY to authenticate agents.
// Agents - these are installed on each server you are monitoring. see: agent.php

if ($action == 'savestats' && $_POST['SHELL_API_KEY'] == $_ENV['SHELL_API_KEY']) {
    $api = new ApiController();
    return $api->dispatch("savestats");

} else if($action == 'savestats') {
    throw new \Exception("Invalid API KEY.");
}

// If the user is not logged in - route to the login action
if (!in_array($action, ["login"])  && !in_array($route, ["authenticate"]) && empty($sessionKey)) {
    $action = 'login';
}

switch($action) {
    case 'api':
        $api = new ApiController();
        $api->dispatch($route);
        break;
    case 'login':
        echo file_get_contents("../templates/login.html");
        break;
    default:
        $hosts = (new StatsModel())->getHostnames();
        $host = $_GET['stat_host'] ?? 'all';
        echo require_once("../templates/dashboard.php");
}