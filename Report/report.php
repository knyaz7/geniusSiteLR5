<?php
include "db/DBManager.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user']['accessright'] == 'guest') {
    header("Location: /index.php"); // Перенаправление на главную страницу, если уровень доступа не соответствует
    exit();
}

if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];
    $selectedYearDisplay = htmlspecialchars($selectedYear);

    // Подключение к базе данных
    $dbManager = new DBManager();
    $conn = $dbManager->dbConnect();

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // SQL-запрос для получения данных
    $sql = "SELECT fm.furniture_name, fm.model_name, s.quantity, fm.model_price,
        (s.quantity * fm.model_price) as model_cost, c.id as contract_id, c.customer_id
        FROM sales s
        INNER JOIN contracts c ON s.contract_id = c.id
        INNER JOIN furniture_model fm ON s.furniture_model_id = fm.id
        INNER JOIN customers cu ON c.customer_id = cu.id
        WHERE YEAR(c.contract_date) = $selectedYear AND s.is_deleted = false";

    $result = $conn->query($sql);

    if ($result === false) {
        echo "Ошибка выполнения SQL-запроса: " . $conn->error;
    } else {
        // Формирование отчета
        echo "<h2>Отчет о выполнении договоров на продажу мебели за $selectedYearDisplay год</h2>";

        $currentContractId = null;
        $totalQuantity = 0;
        $totalCost = 0;
        $totalQuantityAll = 0;
        $totalCostAll = 0;

        echo "<table border='1'>";
        echo "<tr>
                <th>номер договора</th>
                <th>название мебели</th>
                <th>модель</th>
                <th>количество. шт.</th>
                <th>цена модели, руб.</th>
                <th>стоимость модели, руб.</th>
            </tr>";


        while ($row = $result->fetch_assoc()) {
            if ($currentContractId !== $row['contract_id']) {
                // Начало нового раздела с новым номером договора
                if ($currentContractId !== null) {
                    // Вывод общей суммы по предыдущему договору
                    echo "<tr>";
                    echo "<td>Итого по договору:</td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td>$totalQuantity</td>";
                    echo "<td></td>";
                    echo "<td>$totalCost руб.</td>";
                    echo "</tr>";
                    $totalQuantityAll += $totalQuantity;
                    $totalCostAll += $totalCost;
                    $totalQuantity = 0;
                    $totalCost = 0;
                }

                // Вывод номера нового договора
                echo "<tr>";
                echo "<td>" . $row['contract_id'] . "</td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "</tr>";

                $currentContractId = $row['contract_id'];
            }

            // Вывод данных по модели
            echo "<tr>";
            echo "<td></td>";
            echo "<td>" . $row['furniture_name'] . "</td>";
            echo "<td>" . $row['model_name'] . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td>" . $row['model_price'] . "</td>";
            echo "<td>" . $row['model_cost'] . "</td>";
            echo "</tr>";

            $totalQuantity += $row['quantity'];
            $totalCost += $row['model_cost'];
        }

        // Вывод общей суммы по последнему договору
        echo "<tr>";
        echo "<td>Итого по договору:</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td>$totalQuantity</td>";
        echo "<td></td>";
        echo "<td>$totalCost руб.</td>";
        echo "</tr>";

        $totalQuantityAll += $totalQuantity;
        $totalCostAll += $totalCost;

        // Вывод общей суммы по всем договорам
        echo "<tr>";
        echo "<td><b>Итого:</b></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td><b>$totalQuantityAll</b></td>";
        echo "<td></td>";
        echo "<td><b>$totalCostAll руб.</b></td>";
        echo "</tr>";

        echo "</table>";
    }

    $conn->close();
} else {
    echo "Пожалуйста, выберите год для генерации отчета.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Отчет о выполнении договоров на продажу мебели</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        h2 {
            background-color: #3498db;
            color: #fff;
            padding: 20px;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
        }

        th {
            background-color: #3498db;
            color: #fff;
        }

        a.return-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: .2s linear;
        }

        a.return-button:hover {
            background-color: #217dbb;
        }
    </style>
</head>
<body>
    <a class="return-button" href="../<?php echo $_SESSION['user']['accessright']; ?>.php">Вернуться на главную</a>
</body>
</html>