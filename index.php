<?php
use Net\MJDawson\AccountSystem\User;
use Net\MJDawson\AccountSystem\Accounts;

if($_SERVER['REQUEST_METHOD'] === 'POST'){    
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AccountSystem' . DIRECTORY_SEPARATOR . 'main.php';
    
    $host = '';
    $username = '';
    $password = '';
    $database = '';
    
    $additionalVals = [
        'fname' => 'Max',
        'lname' => 'Dawson',
        'admin' => 1
    ];
    
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $accounts = new Accounts($conn, 'users');
    $userAccount = new User($conn, 'users', $_POST['username'], $_POST['password']);
    $user = $userAccount->get();

    if($user !== null){
        echo "Hello ".htmlspecialchars($user['additional_values']['fname']).'!';
    } else{
        echo "Invalid username or password!";
    }
    
    // Close connection
    $conn->close();
} else{
    ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Page</title>
        </head>
        <body>
            <p>Login</p>
            <form action="/" method="POST">
                <label for="username">Username:</label><br>
                <input type="text" id="username" name="username" required><br>

                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required><br>

                <input type="submit" value="Submit">
            </form>
        </body>
        </html>

    <?php
}