<?php
include "db/DBManager.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user']['accessright'] !== 'admin') {
    header("Location: /index.php"); // Перенаправление на главную страницу, если уровень доступа не соответствует
    exit();
}

if (isset($_POST['logout'])) {
    // Удаление сессии
    session_unset();
    session_destroy();

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
    <title>Страница <?php echo $_SESSION['user']['accessright']; ?></title>
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
<h1>Отчет о выполнении договоров на продажу мебели</h1>
    <div class="report_form">
    <form method="post" action="Report/report.php">
        <label for="year">Выберите год:</label>
        <select name="year" id="year">
            <?php
            // Подключение к базе данных
            $dbManager = new DBManager();
            $conn = $dbManager->dbConnect();

            if ($conn->connect_error) {
                die("Ошибка подключения: " . $conn->connect_error);
            }

            // Получение доступных годов из базы данных
            $sql = "SELECT DISTINCT YEAR(contract_date) as year FROM contracts WHERE is_deleted = false";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['year'] . "'>" .$row['year'] . "</option>";
                }
            }

            $conn->close();
            ?>
        </select>
        <input type="submit" value="Сгенерировать отчет">
    </form>
    </div>
    <h1>Управление базой данных</h1>
    <a class="btn" href="CRUD/furniture_model.php">Управление таблицей furniture_model</a>
    <a class="btn" href="CRUD/customers.php">Управление таблицей customers</a>
    <a class="btn" href="CRUD/contracts.php">Управление таблицей contracts</a>
    <a class="btn" href="CRUD/sales.php">Управление таблицей sales</a>
    <h1>Управление пользователями</h1>
    <a class="btn" href="CRUD/users.php">Управление таблицей users</a>
    <p>Выполнен вход: <b><?php echo $_SESSION['user']['accessright']; ?></b></p>
    <form method="post">
        <input type="submit" name="logout" value="Выйти">
    </form>
</body>
</html>
