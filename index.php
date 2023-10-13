<?php session_start();
require_once("./vendor/autoload.php");

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Plexcorp\Monitoring\ApiController;
use Plexcorp\Monitoring\StatsModel;

$action = $_GET['action'] ?? '';
$route = $_GET['route'] ?? '';
$sessionKey = $_SESSION['SESSIONKEY'] ?? '';

if ($action == 'savestats' && $_POST['SHELL_API_KEY'] == $_ENV['SHELL_API_KEY']) {
    $api = new ApiController();
    return $api->dispatch("savestats");

} else if($action == 'savestats') {
    throw new \Exception("Invalid API KEY.");
}

if (!in_array($action, ["login"])  && !in_array($route, ["authenticate"]) && empty($sessionKey)) {
    $action = 'login';
}

switch($action) {
    case 'api':
        $api = new ApiController();
        $api->dispatch($route);
        break;
    case 'login':
        echo file_get_contents("./templates/login.html");
        break;
    default:
        $hosts = (new StatsModel())->getHostnames();
        $host = $_GET['stat_host'] ?? 'all';
        $html = require_once("./templates/dashboard.php");
        echo $html;
}