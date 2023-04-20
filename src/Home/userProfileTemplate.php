<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>User Profile</title>
    </head>
    <body>
        <h1><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p>Email: <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></p>
    </body>
</html>
