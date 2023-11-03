<?php namespace Plexcorp\Monitoring;

class UserModel extends Db
{
    public function saveUser($username, $password)
    {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $saved = $this->saveData("users", [
            'username' => $username,
            'password' => $password,
            'enabled' => 1
        ]);

        if (!empty($saved)) {
            return true;
        }

        return false;
    }

}