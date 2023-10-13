<?php namespace Plexcorp\Monitoring;

class ApiController {
    private $model;

    public function __construct()
    {
        $this->model = new StatsModel();
    }

    public function dispatch($route) {
        return $this->$route();
    }

    public function stats() {
        $stat_host = $_GET['stat_host'] ?? 'all';
        $stats = $this->model->getPercentagStat($_GET['stat_type'], $stat_host);
        header("Content-Type: application/json");
        echo json_encode($stats);
    }


    public function authenticate() {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($username != $_ENV['AUTH_USERNAME'] || $password != $_ENV['AUTH_PASS']) {
            header("Content-Type: application/json");
            echo json_encode(["success" => "no"]);
            return;
        }

        $_SESSION['SESSIONKEY'] = uniqid();

        header("Content-Type: application/json");
        echo json_encode(["success" => "yes"]);
    }

    public function savestats()
    {
        $data = [
            "hostname" => $_POST['hostname'],
            "stat_name" => $_POST['stat_name'],
            "stat_numerical" => $_POST['stat_numerical'],
            "dt_datetime" => $_POST['dt_datetime'],
            "stat_data" => $_POST['stat_data'] ?? ''
        ];

        $this->model->saveData("server_stats", $data);
        header("Content-Type: application/json");
        echo json_encode(["success" => "yes"]);
    }
}