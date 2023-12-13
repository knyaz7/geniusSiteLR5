<?php
include "../db/DBManager.php";
include "../ConfigManager.php";
include "../SessionController.php";
$session = new Session();

if (!$session->has('user')) {
    header("Location: index.php");
    exit();
}

if ($session->get('user')['accessright'] != 'admin') {
    header("Location: /index.php");
    exit();
}

echo '<div class="button-container">';
echo '<a href="../' . $session->get('user')['accessright'] . '.php" class="btn"><=</a>';
echo '</div>';

// Подключение к базе данных
$configManager = new ConfigManager();
$dbManager = new DBManager($configManager->getDBParam());

// Функция для отображения сообщений об операциях
function showMessage($message, $isError = false) {
    echo '<p class="' . ($isError ? 'error' : 'success') . '">' . $message . '</p>';
}

// CREATE - Добавление новой записи
if (isset($_POST['create'])) {
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $customer_phone = $_POST['customer_phone'];

    $result = $dbManager->insert(
        'customers',
        ['customer_name', 'customer_address', 'customer_phone'],
        [$customer_name, $customer_address, $customer_phone]
    );

    if ($result) {
        showMessage("Новая запись успешно добавлена.", false);
    } else {
        showMessage("Ошибка при добавлении записи", true);
    }
}

// DELETE - Логическое удаление записи
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $dbManager->update(
        'customers',
        ['is_deleted' => '1'],
        ['id' => $id]
    );

    if ($result) {
        showMessage("Запись с ID $id успешно удалена (логическое удаление).", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при логическом удалении записи", true);
    }
}

// UPDATE - Редактирование записи
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $is_deleted = 0; 
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $customer_phone = $_POST['customer_phone'];

    $result = $dbManager->update(
        'customers',
        ['customer_name' => $customer_name, 'customer_address' => $customer_address, 'customer_phone' => $customer_phone, 'is_deleted' => $is_deleted],
        ['id' => $id]
    );

    if ($result) {
        showMessage("Запись с ID $id успешно обновлена.", false);
        echo '<script>window.location.href = window.location.pathname;</script>';
    } else {
        showMessage("Ошибка при обновлении записи", true);
    }
}

// READ - Вывод данных из таблицы 
$result = $dbManager->select(
    ['*'],
    'customers',
    ['is_deleted' => 'false'],
);

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
    $result = $dbManager->select(
        ['*'],
        'customers',
        ['id' => $id]
    );

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
$dbManager->closeConnection();
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