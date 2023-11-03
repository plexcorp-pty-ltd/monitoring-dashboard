<?php 
namespace Plexcorp\Monitoring\Console;

use Plexcorp\Monitoring\UserModel;

class MakeUser
{
    private UserModel $model;
    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
        $this->model = new UserModel();
    }

    public function askForUserName() :string
    {
        echo "Please enter a username:" . PHP_EOL;
        $input = fopen("php://stdin","r");

        return fgets($input);
    }

    public function askForPassword() :string
    {
        echo "Please enter a password:" . PHP_EOL;
        $input = fopen("php://stdin","r");

        return fgets($input);
    }


    public function run()
    {
        $username = null;
        $password = null;

        while(true) {
            $username = $this->askForUserName();

            if (strlen($username) < 5) {
                print "Please enter a valid username of at least 8 characters." . PHP_EOL;
                continue;
            }

            break;
        }

        while(true) {

            $password = $this->askForPassword();

            if (strlen($password) < 8) {
                print "Please enter a valid password of at least 8 characters." . PHP_EOL;
                continue;
            }

            break;
        }


        $saved = $this->model->saveUser($username, $password);
        if (!$saved) {
            throw new \Exception("Oops! Something went wrong. Please try again.");
        }

        echo "Successfully setup user: $username". PHP_EOL;

    }

}