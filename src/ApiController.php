<?php namespace Plexcorp\Monitoring;

/**
 * Handles all API requests.
 */
class ApiController {
    private $model;

    public function __construct()
    {
        $this->model = new StatsModel();
    }

    /**
     * $route string - maps to a method name. This is the main API router.
     *
     * @param string $route
     * @return json
     */
    public function dispatch($route) {
        return $this->$route();
    }

    /**
     * All graphs get their data from this endpoint ?action=api&route=stats
     *
     * @return json
     */
    public function stats() {
        $stat_host = $_GET['stat_host'] ?? 'all';
        $stats = $this->model->getPercentagStat($_GET['stat_type'], $stat_host);
        header("Content-Type: application/json");
        echo json_encode($stats);
    }


    /**
     * Checks username and password set in the env.
     *
     * @return json
     */
    public function authenticate() {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $userModel = new UserModel();

        if ($userModel->hasTooManyFailures()) {
            header("Content-Type: application/json");
            echo json_encode(["success" => "no"]);
            return;
        }

        $user = $userModel->getUser($username);

        if (!$user) {
            $userModel->logAuthFailure();
            header("Content-Type: application/json");
            echo json_encode(["success" => "no"]);
            return;
        }

        if (!password_verify($password, $user->password)) {
            $userModel->logAuthFailure();
            header("Content-Type: application/json");
            echo json_encode(["success" => "no"]);
            return;
        }

        $_SESSION['SESSIONKEY'] = uniqid();

        header("Content-Type: application/json");
        echo json_encode(["success" => "yes"]);
    }

    /**
     * Will save a stat posted from the additional nodes.
     *
     * @return json
     */
    public function savestats()
    {
        $data = [
            "hostname" => $_POST['hostname'],
            "stat_name" => $_POST['stat_name'],
            "stat_numerical" => $_POST['stat_numerical'],
            "dt_datetime" => $_POST['dt_datetime'],
            "stat_data" => $_POST['stat_data'] ?? ''
        ];

        $this->model->saveStat($data);

        header("Content-Type: application/json");
        echo json_encode(["success" => "yes"]);
    }
}