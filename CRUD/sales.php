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

// Функция для получения дат контрактов
function getContractDates($selectedId = null) {
    global $dbManager;

    $result = $dbManager->select(
        ['id', 'contract_date'],
        'contracts',
        ['is_deleted' => 'false']
    );

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $selected = ($row['id'] == $selectedId) ? 'selected' : '';
            echo "<option value='" . $row['id'] . "' $selected>" . $row['contract_date'] . "</option>";
        }
    }
}

// Функция для получения модели мебели
function getFurnitureModels($selectedId = null) {
    global $dbManager;

    $result = $dbManager->select(
        ['id', 'model_name'],
        'furniture_models',
        ['is_deleted' => 'false']
    );

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

    $result = $dbManager->insert(
        'sales',
        ['contract_id', 'furniture_model_id', 'quantity'],
        [$contract_id, $furniture_model_id, $quantity]
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
        'sales',
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
    $contract_id = $_POST['contract_id'];
    $furniture_model_id = $_POST['furniture_model_id'];
    $quantity = $_POST['quantity'];
    
    $result = $dbManager->update(
        'sales',
        ['contract_id' => $contract_id, 'furniture_model_id' => $furniture_model_id, 'quantity' => $quantity, 'is_deleted' => $is_deleted],
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
    ['sales.id', 'contracts.contract_date', 'furniture_models.model_name', 'quantity'],
    'sales',
    ['sales.is_deleted' => 'false'],
    [
        ['contracts', 'contracts', 'sales'], ['furniture_models', 'furniture_models', 'sales']
    ]
);

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

    $result = $dbManager->select(
        ['*'],
        'sales',
        ['id' => $id]
    );

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