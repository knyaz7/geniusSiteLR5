<?php
include "../db/DBManager.php";
include "../ConfigManager.php";
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
$configManager = new ConfigManager();
$dbManager = new DBManager($configManager->getDBParam());

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

    $result = $dbManager->insert(
        'furniture_models',
        ['furniture_name', 'model_name', 'model_characteristics', 'model_price'],
        [$furniture_name, $model_name, $model_characteristics, $model_price]
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
        'furniture_models',
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
    $furniture_name = $_POST['furniture_name'];
    $model_name = $_POST['model_name'];
    $model_characteristics = $_POST['model_characteristics'];
    $model_price = $_POST['model_price'];

    $result = $dbManager->update(
        'furniture_models',
        ['furniture_name' => $furniture_name, 'model_name' => $model_name, 'model_characteristics' => $model_characteristics, 'model_price' => $model_price, 'is_deleted' => $is_deleted],
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
    'furniture_models',
    ['is_deleted' => '0'],
);

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
    $result = $dbManager->select(
        ['*'],
        'furniture_models',
        ['id' => $id]
    );

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
$dbManager->closeConnection();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Управление таблицей furniture_models</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
</body>
</html>