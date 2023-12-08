<!DOCTYPE html>
<html>
<head>
    <title>Главная страница</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
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
    <?php
    session_start();

    if (isset($_SESSION['user'])) {
        // Пользователь авторизован, перенаправляем его на соответствующую страницу
        header("Location: {$_SESSION['user']['accessright']}.php");
        exit();
    }

    // Подключение к базе данных 
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "furniture";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Ошибка подключения к базе данных: " . $conn->connect_error);
    }

    if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_password'])) {
        $email = $_COOKIE['user_email'];
        $password = $_COOKIE['user_password'];
    
        // Проверяем, существует ли уже такой email в базе данных
        $checkEmailQuery = "SELECT id, email, password, accessright FROM users WHERE email = ?";
        $checkEmailStmt = $conn->prepare($checkEmailQuery);
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailResult = $checkEmailStmt->get_result();
    
        if ($checkEmailResult->num_rows > 0) {
            $row = $checkEmailResult->fetch_assoc();
    
            if ($password === $row['password']) {
                session_start();
                $_SESSION['user'] = [
                    'id' => $row['id'],
                    'email' => $row['email'],
                    'accessright' => $row['accessright']
                ];
    
                // Перенаправляем пользователя на соответствующую страницу
                header("Location: {$row['accessright']}.php");
                exit();
            }
        }
    }
    ?>
    <h1>База данных "Мебель"</h1>
    <p>Использовать строго в корпоративных целях!</p>
    <a class="btn" href="register.php">Зарегистрироваться</a>
    <a class="btn" href="login.php">Войти</a>
</body>
</html>
