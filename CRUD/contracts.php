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

// Функция для получения имен клиентов
function getCustomerNames($selectedId = null) {
    global $dbManager;

    $result = $dbManager->select(
        ['id', 'customer_name'],
        'customers',
        ['is_deleted' => 'false']
    );

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $selected = ($row['id'] == $selectedId) ? 'selected' : '';
            echo "<option value='" . $row['id'] . "' $selected>" . $row['customer_name'] . "</option>";
        }
    }
}

// CREATE - Добавление новой записи
if (isset($_POST['create'])) {
    $customer_id = $_POST['customer_id'];
    $contract_date = new DateTime($_POST['contract_date']);
    $contract_completion_date = new DateTime($_POST['contract_completion_date']);
    $contract_date_str = $contract_date->format('Y-m-d H:i:s');
    $contract_completion_date_str = $contract_completion_date->format('Y-m-d H:i:s');

    $result = $dbManager->insert(
        'contracts',
        ['customer_id', 'contract_date', 'contract_completion_date'],
        [$customer_id, $contract_date_str, $contract_completion_date_str]
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
        'contracts',
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
    $customer_id = $_POST['customer_id'];
    $contract_date = new DateTime($_POST['contract_date']);
    $contract_completion_date = new DateTime($_POST['contract_completion_date']);
    $contract_date_str = $contract_date->format('Y-m-d H:i:s');
    $contract_completion_date_str = $contract_completion_date->format('Y-m-d H:i:s');

    $result = $dbManager->update(
        'contracts',
        ['customer_id' => $customer_id, 'contract_date' => $contract_date_str, 'contract_completion_date' => $contract_completion_date_str, 'is_deleted' => $is_deleted],
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
    ['contracts.id', 'customers.customer_name', 'contract_date', 'contract_completion_date'],
    'contracts',
    ['contracts.is_deleted' => 'false'],
    [
        ['customers', 'customers', 'contracts']
    ]
);

if ($result->num_rows > 0) {
    echo '<h2>Список договоров:</h2>';
    echo '<table>';
    echo '<tr><th>Номер договора</th><th>Имя клиента</th><th>Дата заключения договора</th><th>Дата исполнения договора</th><th>Действия</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['id'] . '</td>';
        echo '<td>' . $row['customer_name'] . '</td>';
        echo '<td>' . $row['contract_date'] . '</td>';
        echo '<td>' . $row['contract_completion_date'] . '</td>';
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
echo 'Имя клиента: <select name="customer_id" id="customer_id">';
getCustomerNames();
echo '</select><br>';
echo 'Дата заключения договора: <input type="text" name="contract_date"><br>';
echo 'Дата исполнения договора: <input type="text" name="contract_completion_date"><br>';
echo '<input type="submit" name="create" value="Добавить">';
echo '</form>';

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $dbManager->select(
        ['*'],
        'contracts',
        ['id' => $id]
    );

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        echo '<div id="editContainer">';
        echo '<h2>Редактировать запись:</h2>';
        echo '<form method="post" id="editForm">';
        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
        echo 'Имя клиента: <select name="customer_id" id="customer_id">';
        getCustomerNames($row['customer_id']);
        echo '</select><br>';
        echo 'Дата заключения договора: <input type="text" name="contract_date" value="' . $row['contract_date'] . '"><br>';
        echo 'Дата исполнения договора: <input type="text" name="contract_completion_date" value="' . $row['contract_completion_date'] . '"><br>';
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
    <title>Управление таблицей contracts</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
</body>
</html>