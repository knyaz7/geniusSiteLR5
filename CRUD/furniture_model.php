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

// CREATE - Добавление новой записи
if (isset($_POST['create'])) {
    $furniture_name = $_POST['furniture_name'];
    $model_name = $_POST['model_name'];
    $model_characteristics = $_POST['model_characteristics'];
    $model_price = $_POST['model_price'];

    $sql = "INSERT INTO furniture_model (furniture_name, model_name, model_characteristics, model_price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $furniture_name, $model_name, $model_characteristics, $model_price);

    if ($stmt->execute()) {
        showMessage("Новая запись успешно добавлена.", false);
    } else {
        showMessage("Ошибка при добавлении записи: " . $stmt->error, true);
    }
}

// DELETE - Логическое удаление записи
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE furniture_model SET is_deleted = true WHERE id = ?";
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
    $furniture_name = $_POST['furniture_name'];
    $model_name = $_POST['model_name'];
    $model_characteristics = $_POST['model_characteristics'];
    $model_price = $_POST['model_price'];

    $sql = "UPDATE furniture_model SET furniture_name = ?, model_name = ?, model_characteristics = ?, model_price = ?, is_deleted = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $furniture_name, $model_name, $model_characteristics, $model_price, $is_deleted, $id);

    if ($stmt->execute()) {
        showMessage("Запись с ID $id успешно обновлена.", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при обновлении записи: " . $stmt->error, true);
    }
}

// READ - Вывод данных из таблицы 
$sql = "SELECT * FROM furniture_model WHERE is_deleted = false";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<h2>Список моделей мебели:</h2>';
    echo '<table>';
    echo '<tr><th>Название мебели</th><th>Название модели</th><th>Характеристики</th><th>Цена</th><th>Действия</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['furniture_name'] . '</td>';
        echo '<td>' . $row['model_name'] . '</td>';
        echo '<td>' . $row['model_characteristics'] . '</td>';
        echo '<td>' . $row['model_price'] . '</td>';
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
echo 'Название мебели: <input type="text" name="furniture_name"><br>';
echo 'Название модели: <input type="text" name="model_name"><br>';
echo 'Характеристики: <input type="text" name="model_characteristics"><br>';
echo 'Цена: <input type="text" name="model_price"><br>';
echo '<input type="submit" name="create" value="Добавить">';
echo '</form>';

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM furniture_model WHERE id = ?";
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
        echo 'Название мебели: <input type="text" name="furniture_name" value="' . $row['furniture_name'] . '"><br>';
        echo 'Название модели: <input type="text" name="model_name" value="' . $row['model_name'] . '"><br>';
        echo 'Характеристики: <input type="text" name="model_characteristics" value="' . $row['model_characteristics'] . '"><br>';
        echo 'Цена: <input type="text" name="model_price" value="' . $row['model_price'] . '"><br>';
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
    <title>Управление таблицей furniture_model</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
</body>
</html>