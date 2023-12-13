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
    include "db/DBManager.php";
    include "ConfigManager.php";
    include "SessionController.php";
    $session = new Session();

    if ($session->has('user')) {
        // Пользователь авторизован, перенаправляем его на соответствующую страницу
        header("Location: {$session->get('user')['accessright']}.php");
        exit();
    }

    // Подключение к базе данных 
    $configManager = new ConfigManager();
    $dbManager = new DBManager($configManager->getDBParam());
    
    if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_password'])) {
        $email = $_COOKIE['user_email'];
        $password = $_COOKIE['user_password'];
    
        // Проверяем, существует ли уже такой email в базе данных
        $dbManager->select(
            ['id', 'email', 'password', 'accessright'],
            'users',
            [
                'email' => $email
            ]
        );
    
        if ($checkEmailResult->num_rows > 0) {
            $row = $checkEmailResult->fetch_assoc();
    
            if ($password === $row['password']) {
                $session->set('user', [
                    'id' => $row['id'],
                    'email' => $row['email'],
                    'accessright' => $row['accessright']
                ]);

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
    <!-- <?php
        include "SuperUser.php";
        try {
            $user = new User('marina@mail.ru', 'lol');
            $user2 = clone $user;
            $superUser = new SuperUser('admin');
            echo '<p>' . $user->showInfo() . '</p>';
            echo '<p>' . print_r($superUser) . '</p>';

            $user = new User();
        } catch (Exception $e) {
            echo '<p>' . $e->getMessage() . '</p>';
        }
    ?> -->
</body>
</html>
