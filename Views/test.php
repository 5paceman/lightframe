<?php

use App\Authentication\Authenticate;
use Models\User;
use function App\Authentication\user;

?>

<html>
    <body>
        <?php
            if(Authenticate::authed())
            {
                echo '<b>Logged In!</b> <a href="/logout">Logout</a>';
                $user = user();
                echo "{$user->email}";
            }
        ?>
        <form action="/login" method="post">
            <h1>Login</h1>
            <label>Email</label>
            <input name="email">
            <label>Password</label>
            <input type="password" name="password">
            <input type="submit" value="Login">
        </form>
        <a href="/google-login">Login with Google</a>
        <form action="/register" method="post">
            <h1>Register</h1>
            <label>Email</label>
            <input name="email">
            <label>Password</label>
            <input type="password" name="password">
            <input type="submit" value="Register">
        </form>

        <?php
            var_dump(User::all());
        ?>
    </body>
</html>