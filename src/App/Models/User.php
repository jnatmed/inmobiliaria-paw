<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class User extends Model
{
    private $email;
    private $contrasenia;

    public function __construct($email, $contrasenia)
    {
        $this->email = $email;
        $this->contrasenia = $contrasenia;
    }

    // Getter for email
    public function getEmail()
    {
        return $this->email;
    }

    // Setter for email
    public function setEmail($email)
    {
        $this->email = $email;
    }

    // Getter for contrasenia
    public function getContrasenia()
    {
        return $this->contrasenia;
    }

    // Setter for contrasenia
    public function setContrasenia($contrasenia)
    {
        $this->contrasenia = $contrasenia;
    }
}
