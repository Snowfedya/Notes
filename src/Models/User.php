<?php

namespace App\Models;

class User
{
    public $id;
    public $email;
    public $password_hash;
    public $name;
    public $avatar_url;
    public $created_at;
    public $updated_at;
    public $last_login;
    public $settings;

    public function __construct($email, $password, $name)
    {
        $this->email = $email;
        $this->password_hash = password_hash($password, PASSWORD_BCRYPT);
        $this->name = $name;
    }
}
