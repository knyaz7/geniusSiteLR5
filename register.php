<?php
include "db/DBManager.php";
include "ConfigManager.php";
// Подключение к базе данных 
$configManager = new ConfigManager();
$dbManager = new DBManager($configManager->getDBParam());

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) > 6;
}

// Обработка данных регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверяем, существует ли уже такой email в базе данных
    // $checkEmailQuery = "SELECT id FROM users WHERE email = ?";
    // $checkEmailStmt = $conn->prepare($checkEmailQuery);
    // $checkEmailStmt->bind_param("s", $email);
    // $checkEmailStmt->execute();
    // $checkEmailResult = $checkEmailStmt->get_result();
    $checkEmailResult = $dbManager->select(
        ['id'],
        'users',
        ['email' => $email]
    );

    if ($checkEmailResult->num_rows > 0) {
        echo "Пользователь с таким email уже существует.";
    } elseif (!validateEmail($email)) {
        echo "Неверный формат email.";
    } elseif (!validatePassword($password)) {
        echo "Пароль должен содержать более 6 символов.";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $accessright = 'guest'; // Устанавливаем уровень "guest" для новых пользователей

        // $sql = "INSERT INTO users (email, password, accessright) VALUES (?, ?, ?)";
        // $stmt = $conn->prepare($sql);
        // $stmt->bind_param("sss", $email, $password, $accessright);
        $stmt = $dbManager->insert(
            'users',
            ['email', 'password', 'accessright'],
            [$email, $password, $accessright]
        );

        if ($stmt) {
            echo "Регистрация успешна. <a href='login.php'>Войти</a>";
        } else {
            echo "Ошибка при регистрации: ";
        }
    }
}

$dbManager->closeConnection();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #fff;
            padding: 20px;
            margin: 50px 0 50px 0; 
            background-color: #3498db;
        }

        .btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: .1s linear;
        }

        .btn:hover {
            background-color: #217dbb;
        }

        form {
            margin: 20px;
        }

        label {
            font-weight: bold;
        }

        select {
            padding: 5px;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: #fff;
            transition: .2s linear;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            
        }

        input[type="submit"]:hover {
            background-color: #217dbb;
        }

        p {
            margin-left: 15px;
        }

        .report_form {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Регистрация</h1>
    <form method="post">
        <label for="email">E-mail:</label>
        <input type="text" name="email" id="email" required>
        <br>
        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <input type="submit" value="Зарегистрироваться">
    </form>
    <a class="btn" href="index.php">Вернуться на главную</a>
</body>
</html>
