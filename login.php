<?php
include "db/connect.php";
// Подключение к базе данных 
$dbParams = dbConnect();
$conn = new mysqli(
    $dbParams['servername'],
    $dbParams['username'],
    $dbParams['password'],
    $dbParams['database']
);

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Обработка данных авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Проверка reCAPTCHA https://www.google.com/recaptcha/admin/site/686721336/setup
        $recaptchaSecretKey = "6Lc4ie4oAAAAAPpSWRGV6IJRV8JCk4_Cu1Zb5-BE"; 
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecretKey&response=$recaptchaResponse";
        $recaptchaData = json_decode(file_get_contents($recaptchaUrl));
        
        if ($recaptchaData->success) {

            // Проверяем, существует ли уже такой email в базе данных
            $checkEmailQuery = "SELECT id, email, password, accessright FROM users WHERE email = ?";
            $checkEmailStmt = $conn->prepare($checkEmailQuery);
            $checkEmailStmt->bind_param("s", $email);
            $checkEmailStmt->execute();
            $checkEmailResult = $checkEmailStmt->get_result();

            if ($checkEmailResult->num_rows > 0) {
                $row = $checkEmailResult->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    session_start();
                    $_SESSION['user'] = [
                        'id' => $row['id'],
                        'email' => $row['email'],
                        'accessright' => $row['accessright']
                    ];

                    // Проверяем, установлен ли флажок "Запомнить меня"
                    if (isset($_POST['remember_me'])) {
                        // Устанавливаем cookie для запоминания пользователя
                        setcookie('user_email', $row['email'], time() + 86400 * 30); // На месяц
                        setcookie('user_password', $row['password'], time() + 86400 * 30);
                    }

                    header("Location: {$row['accessright']}.php");
                    exit();
                } else {
                    echo "Неверный пароль.";
                }
            } else {
                echo "Пользователь с таким email не найден.";
            }

            $checkEmailStmt->close();
        } else {
            echo "Пожалуйста, подтвердите, что вы не робот.";
        }
    } 
}

$conn->close();
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
        <div class="g-recaptcha" data-sitekey="6Lc4ie4oAAAAAOPEqod_d_HSgwHFao47QrcCV2eW"></div>
        <br/>
        <input type="submit" value="Войти">
    </form>
    <a class="btn" href="index.php">Вернуться на главную</a>
</body>
</html>
