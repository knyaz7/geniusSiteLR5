<?php
include "SessionController.php";
$session = new Session();

if (!$session->has('user')) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['logout'])) {
    // Удаление сессии
    $session->destroy();

    // Удаление cookie "user_email" и "user_password"
    setcookie('user_email', '', time() - 3600);
    setcookie('user_password', '', time() - 3600);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Страница <?php echo $session->get('user')['accessright']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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

        h1 {
            text-align: center;
            color: #fff;
            padding: 20px;
            margin: 50px 0 50px 0; 
            background-color: #3498db;
        }
    </style>
</head>
<body>
    <h1>База данных "Мебель"</h1>
    <p>Разработчик - Владимир Наумов</p>
    <p>Выполнен вход: <b><?php echo $session->get('user')['accessright']; ?></b></p>
    <p>Администратор скоро рассмотрит вашу заявку, ожидайте.</p>
    <form method="post">
        <input type="submit" name="logout" value="Выйти">
    </form>
</body>
</html>
