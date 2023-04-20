<?php

namespace App\Home;

class UserView
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Render user profile.
     *
     * @return string
     */
    public function render(): string
    {
        $name = $this->user->getName();
        $email = $this->user->getEmail();

        ob_start();
        include 'userProfileTemplate.php';

        return ob_get_clean();
    }
}
