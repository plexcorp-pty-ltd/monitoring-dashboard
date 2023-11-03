<?php namespace Plexcorp\Monitoring;

class UserModel extends Db
{
    public function saveUser($username, $password)
    {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $saved = $this->saveData("users", [
            'username' => trim($username),
            'password' => $password,
            'enabled' => 1,
        ]);

        if (!empty($saved)) {
            return true;
        }

        return false;
    }

    public function getUser($username)
    {
        return $this->fetchOne("SELECT id,password FROM users where username=? and enabled=1", [
            $username,
        ]);
    }

    public function logAuthFailure()
    {
        $this->saveData("failed_logins", [
            "ip_address" => $_SERVER['REMOTE_ADDR'],
            "last_attempt" => date("Y-m-d H:i:s"),
        ]);
    }

    public function hasTooManyFailures()
    {
        $dtime = date("Y-m-d H:i:s", strtotime("-15 minutes"));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM failed_logins WHERE ip_address = ? AND last_attempt >= ?");
        $stmt->execute([
            $_SERVER['REMOTE_ADDR'],
            $dtime,
        ]);

        $total = $stmt->fetchColumn(0);

        if ($total >= (int) $_ENV["MAX_FAILED_LOGIN_ATTEMPTS"]) {
            return true;
        }

        return false;
    }

}
