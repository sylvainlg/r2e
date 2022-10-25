<?php

namespace App\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

class ChangePassword
{
    #[SecurityAssert\UserPassword(
        message: "La saisie de votre mot de passe courant est éronée"
    )]
    public $oldPassword;

    #[
        RollerworksPassword\PasswordStrength(minLength: 7, minStrength: 3),
        Assert\NotBlank,
        Assert\NotIdenticalTo(
            message: "Le nouveau mot de passe doit être différent de l'ancien",
            propertyPath: "oldPassword"
        )
    ]
    public $newPassword;
}
