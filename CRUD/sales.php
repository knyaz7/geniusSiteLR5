<?php
include "db/DBManager.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user']['accessright'] != 'admin') {
    header("Location: /index.php"); // Перенаправление на главную страницу, если уровень доступа не соответствует
    exit();
}

echo '<div class="button-container">';
echo '<a href="../' . $_SESSION['user']['accessright'] . '.php" class="btn"><=</a>';
echo '</div>';


// Подключение к базе данных
$dbManager = new DBManager();
$conn = $dbManager->dbConnect();

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Функция для отображения сообщений об операциях
function showMessage($message, $isError = false) {
    echo '<p class="' . ($isError ? 'error' : 'success') . '">' . $message . '</p>';
}

// Функция для получения дат контрактов
function getContractDates($selectedId = null) {
    global $conn;
    $sql = "SELECT id, contract_date FROM contracts WHERE is_deleted = false";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $selected = ($row['id'] == $selectedId) ? 'selected' : '';
            echo "<option value='" . $row['id'] . "' $selected>" . $row['contract_date'] . "</option>";
        }
    }
}

// Функция для получения модели мебели
function getFurnitureModels($selectedId = null) {
    global $conn;
    $sql = "SELECT id, model_name FROM furniture_model WHERE is_deleted = false";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $selected = ($row['id'] == $selectedId) ? 'selected' : '';
            echo "<option value='" . $row['id'] . "' $selected>" . $row['model_name'] . "</option>";
        }
    }
}

// CREATE - Добавление новой записи
if (isset($_POST['create'])) {
    $contract_id = $_POST['contract_id'];
    $furniture_model_id = $_POST['furniture_model_id'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO sales (contract_id, furniture_model_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $contract_id, $furniture_model_id, $quantity);

    if ($stmt->execute()) {
        showMessage("Новая запись успешно добавлена.", false);
    } else {
        showMessage("Ошибка при добавлении записи: " . $stmt->error, true);
    }
}

// DELETE - Логическое удаление записи
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE sales SET is_deleted = true WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        showMessage("Запись с ID $id успешно удалена (логическое удаление).", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при логическом удалении записи: " . $stmt->error, true);
    }
}

// UPDATE - Редактирование записи
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $is_deleted = 0; 
    $contract_id = $_POST['contract_id'];
    $furniture_model_id = $_POST['furniture_model_id'];
    $quantity = $_POST['quantity'];
    
    $sql = "UPDATE sales SET contract_id = ?, furniture_model_id = ?, quantity = ?, is_deleted = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $contract_id, $furniture_model_id, $quantity, $is_deleted, $id);

    if ($stmt->execute()) {
        showMessage("Запись с ID $id успешно обновлена.", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при обновлении записи: " . $stmt->error, true);
    }
}

// READ - Вывод данных из таблицы 
$sql = "SELECT sales.id, contracts.contract_date, furniture_model.model_name, quantity
        FROM sales
        JOIN contracts ON sales.contract_id = contracts.id
        JOIN furniture_model ON sales.furniture_model_id = furniture_model.id
        WHERE sales.is_deleted = false";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<h2>Список продаж:</h2>';
    echo '<table>';
    echo '<tr><th>Дата заключения договора</th><th>Модель мебели</th><th>Количество</th><th>Действия</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['contract_date'] . '</td>';
        echo '<td>' . $row['model_name'] . '</td>';
        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td><a href="?delete=' . $row['id'] . '">Логически удалить</a> | <a href="?edit=' . $row['id'] . '">Изменить</a></td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    showMessage("Таблица пуста.");
}

// Форма для добавления новой записи
echo '<h2>Добавить новую запись:</h2>';
echo '<form method="post">';
echo 'Дата заключения договора: <select name="contract_id" id="contract_id">';
getContractDates();
echo '</select><br>';
echo 'Модель мебели: <select name="furniture_model_id" id="furniture_model_id">';
getFurnitureModels();
echo '</select><br>';
echo 'Количество: <input type="text" name="quantity"><br>';
echo '<input type="submit" name="create" value="Добавить">';
echo '</form>';

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM sales WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        echo '<div id="editContainer">';
        echo '<h2>Редактировать запись:</h2>';
        echo '<form method="post" id="editForm">';
        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
        echo 'Дата заключения договора: <select name="contract_id" id="contract_id">';
        getContractDates($row['contract_id']);
        echo '</select><br>';
        echo 'Модель мебели: <select name="furniture_model_id" id="furniture_model_id">';
        getFurnitureModels($row['furniture_model_id']);
        echo '</select><br>';
        echo 'Количество: <input type="text" name="quantity" value="' . $row['quantity'] . '"><br>';
        echo '<input type="submit" name="update" value="Сохранить">';
        echo '</form>';
        echo '</div>'; 
    }
}


// Закрытие соединения с базой данных
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Управление таблицей contracts</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
</body>
</html>