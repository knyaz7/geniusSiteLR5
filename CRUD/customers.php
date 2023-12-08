<?php
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
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "furniture";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Функция для отображения сообщений об операциях
function showMessage($message, $isError = false) {
    echo '<p class="' . ($isError ? 'error' : 'success') . '">' . $message . '</p>';
}

// CREATE - Добавление новой записи
if (isset($_POST['create'])) {
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $customer_phone = $_POST['customer_phone'];

    $sql = "INSERT INTO customers (customer_name, customer_address, customer_phone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $customer_name, $customer_address, $customer_phone);

    if ($stmt->execute()) {
        showMessage("Новая запись успешно добавлена.", false);
    } else {
        showMessage("Ошибка при добавлении записи: " . $stmt->error, true);
    }
}

// DELETE - Логическое удаление записи
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE customers SET is_deleted = true WHERE id = ?";
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
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $customer_phone = $_POST['customer_phone'];

    $sql = "UPDATE customers SET customer_name = ?, customer_address = ?, customer_phone = ?, is_deleted = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $customer_name, $customer_address, $customer_phone, $is_deleted, $id);

    if ($stmt->execute()) {
        showMessage("Запись с ID $id успешно обновлена.", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при обновлении записи: " . $stmt->error, true);
    }
}

// READ - Вывод данных из таблицы 
$sql = "SELECT * FROM customers WHERE is_deleted = false";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<h2>Список клиентов:</h2>';
    echo '<table>';
    echo '<tr><th>Имя клиента</th><th>Адрес клиента</th><th>Телефон клиента</th><th>Действия</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['customer_name'] . '</td>';
        echo '<td>' . $row['customer_address'] . '</td>';
        echo '<td>' . $row['customer_phone'] . '</td>';
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
echo 'Имя клиента: <input type="text" name="customer_name"><br>';
echo 'Адрес клиента: <input type="text" name="customer_address"><br>';
echo 'Телефон клиента: <input type="text" name="customer_phone"><br>';
echo '<input type="submit" name="create" value="Добавить">';
echo '</form>';

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM customers WHERE id = ?";
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
        echo 'Имя клиента: <input type="text" name="customer_name" value="' . $row['customer_name'] . '"><br>';
        echo 'Адрес клиента: <input type="text" name="customer_address" value="' . $row['customer_address'] . '"><br>';
        echo 'Телефон клиента: <input type="text" name="customer_phone" value="' . $row['customer_phone'] . '"><br>';
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
    <title>Управление таблицей customers</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
</body>
</html>