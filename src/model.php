<?php
class Model
{
}

class User
{
    public $id;
    public $name;
    public $email;
    public $url;

    public function __construct(int $id, string $name, string $email, string $url)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->url= $url;
    }

    public function to_str(): string
    {
        return "Hello, my name $this->name, my email: $this->email";
    }

}