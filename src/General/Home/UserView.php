<?php

namespace App\General\Home;

use App\Util\Template\Template;

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

        $pagina = new Template('userProfileTemplate');

        Template::put($html, 'name', $name);
        Template::put($html, 'email', $email);

        return $html;
    }
}
