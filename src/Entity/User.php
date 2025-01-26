<?php

namespace Enzo\P5OcBlog\Entity;

class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private string $password;
    private string $role;
    private $createdAt;

    public function __construct(?int $id, $username, $email, $password, $role, $createdAt)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
