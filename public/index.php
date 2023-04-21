<?php

require_once '/../vendor/autoload.php';

use App\General\Home\User;
use App\General\Home\UserView;

$user = new User();
$user->setName('John Doe');
$user->setEmail('john.doe@example.com');

$userProfile = new UserView($user);
$html = $userProfile->render();

echo $html;
