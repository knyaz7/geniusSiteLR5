<?php
include "db/DBManager.php";
include "ConfigManager.php";
include "SessionController.php";

$session = new Session();
$flash = new Flash($session);

// Подключение к базе данных 
$configManager = new ConfigManager();
$dbManager = new DBManager($configManager->getDBParam());

if ($session->has('user')) {
    header("Location: {$session->get('user')['accessright']}.php");
    exit();
}
// Обработка данных авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Проверка reCAPTCHA https://www.google.com/recaptcha/admin/site/
    $recaptchaSecretKey = "6LdkuCspAAAAALOghDiJLE3-iwcEwpK2FuKItVGx"; 
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecretKey&response=$recaptchaResponse";
    $recaptchaData = json_decode(file_get_contents($recaptchaUrl));
    
    if ($recaptchaData->success) {
        // Проверяем, существует ли уже такой email в базе данных
        $checkEmailResult = $dbManager->select(
            ['id', 'email', 'password', 'accessright'],
            'users',
            ['email' => $email]
        );

        if ($checkEmailResult->num_rows > 0) {
            $row = $checkEmailResult->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $session->set('user', [
                    'id' => $row['id'],
                    'email' => $row['email'],
                    'accessright' => $row['accessright']
                ]);

                // Проверяем, установлен ли флажок "Запомнить меня"
                if (isset($_POST['remember_me'])) {
                    // Устанавливаем cookie для запоминания пользователя
                    setcookie('user_email', $row['email'], time() + 86400 * 30); // На месяц
                    setcookie('user_password', $row['password'], time() + 86400 * 30);
                }
                
                $flash->setMessage("Вход прошел успешно.");

                header("Location: {$row['accessright']}.php");
                exit();
            } else {
                echo "Неверный пароль.";
            }
        } else {
            echo "Пользователь с таким email не найден.";
        }

        // $checkEmailStmt->close();
    } else {
        echo "Пожалуйста, подтвердите, что вы не робот.";
    }
} 


$dbManager->closeConnection();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            padding: 10px;
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
    <h1>Вход</h1>
    <form method="post">
        <label for="email">E-mail:</label>
        <input type="text" name="email" id="email" required>
        <br>
        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <label for="remember_me">Запомнить меня:</label>
        <input type="checkbox" name="remember_me" id="remember_me">
        <br>
        <div class="g-recaptcha" data-sitekey="6LdkuCspAAAAAB0Y-5UOhsMoT2auGQsLRPVjFT8c"></div>
        <br/>
        <input type="submit" value="Войти">
    </form>
    <a class="btn" href="index.php">Вернуться на главную</a>
</body>
</html>
