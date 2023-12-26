<?php

class UserController
{
    public $users;

    public function __construct(int $n=10)
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < $n; $i++) {
            $name = $faker->name;
            $mail = substr(str_replace(" ", "", $name), 0, 12) . "@mail.ru";
            $url = $faker->imageUrl;
            $this->users[] = new User($i, $name, $mail, $url);

        }
    }

    public function getUser(): array
    {
        return $this->users;
    }

    public function getConcreteUser(int $id, int $lim): array
    {
        if ($id>$lim-1 or $id<0)
            return [];
        return array($this->users[$id]);
    }

    public function getUserDb(): array
    {
        $sql = "SELECT * FROM users";

        try {
            $db = new Db();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            return $customers;
        } catch (PDOException $e) {
            $error = array(
                "message" => $e->getMessage()
            );

            return $error;
        }
    }

    public function createUser($name, $email): array
    {
        $arr = array();

        if (!empty($name && !empty($email))) {

            $arr["name"] = $name;
            $arr['email'] = $email;
            $model = new User($name, $email);
            $arr['message'] = $model->to_str();
        } else {
            $arr['message'] = "you have not posted any data";
        }
        return $arr;
    }

    public function updateUser($name, $email): array
    {
        $arr = array();

        if (!empty($name && !empty($email))) {

            $arr["name"] = $name;
            $arr['email'] = $email;
            $model = new User($name, $email);
            $arr['message'] = $model->to_str();
        } else {
            $arr['message'] = "you have not posted any data";
        }
        return $arr;
    }
}

function checkLimit($n): int
{
    if (!array_key_exists('limit', $n)) {
        return 5;
    }
    elseif (is_int((int)$n['limit']) and $n['limit'] >= 1 and $n['limit'] < 11)
    {
        return (int)$n['limit'];
    } else return 5;

}