<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Home\User;
use App\Home\UserView;

$user = new User();
$user->setName('John Doe');
$user->setEmail('john.doe@example.com');

$userProfile = new UserView($user);
$html = $userProfile->render();

echo $html;
