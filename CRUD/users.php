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

// Функция для получения уровня пользователей
function echoUserTypes($userType = null) {
    $userTypes = ["guest", "operator", "admin"];
    for ($i = 0; $i < count($userTypes); $i++) {
        $selected = ($userTypes[$i] == $userType) ? 'selected' : '';
        echo "<option value='" . $userTypes[$i] . "' $selected>" . $userTypes[$i] . "</option>";
    }
}

// DELETE - Логическое удаление записи
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE users SET is_deleted = true WHERE id = ?";
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
    $email = $_POST['email'];
    $accessright = $_POST['accessright'];
    
    $sql = "UPDATE users SET email = ?, accessright = ?, is_deleted = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $email, $accessright, $is_deleted, $id);

    if ($stmt->execute()) {
        showMessage("Запись с ID $id успешно обновлена.", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при обновлении записи: " . $stmt->error, true);
    }
}

// READ - Вывод данных из таблицы 
$sql = "SELECT id, email, accessright FROM users WHERE is_deleted = false";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<h2>Список пользователей:</h2>';
    echo '<table>';
    echo '<tr><th>Почта пользователя</th><th>Статус пользователя</th><th>Действия</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['email'] . '</td>';
        echo '<td>' . $row['accessright'] . '</td>';
        echo '<td><a href="?delete=' . $row['id'] . '">Логически удалить</a> | <a href="?edit=' . $row['id'] . '">Изменить</a></td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    showMessage("Таблица пуста.");
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM users WHERE id = ?";
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
        echo 'Почта пользователя: <input type="text" name="email" value="' . $row['email'] . '"><br>';
        echo 'Статус пользователя: <select name="accessright" id="accessright">';
        echoUserTypes($row['accessright']);
        echo '</select><br>';
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
    <title>Управление таблицей users</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
</body>
</html>