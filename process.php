<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "user_database";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type'])) {
    $form_type = $_POST['form_type'];

    if ($form_type == "form1") {
        if (!isset($_POST['firstName'], $_POST['lastName'], $_POST['email'])) {
            echo "Ошибка: Не все поля заполнены!";
            exit();
        }

        $firstName = $conn->real_escape_string($_POST['firstName']);
        $lastName = $conn->real_escape_string($_POST['lastName']);
        $email = $conn->real_escape_string($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Ошибка: Некорректный email!";
            exit();
        }

        $sql = "INSERT INTO users (first_name, last_name, email) VALUES ('$firstName', '$lastName', '$email')";

        if ($conn->query($sql) === TRUE) {
            echo "Данные успешно сохранены!";
        } else {
            echo "Ошибка: " . $conn->error;
        }

    } elseif ($form_type == "form2") {
        if (!isset($_POST['comment'])) {
            echo "Ошибка: Поле комментария пустое!";
            exit();
        }

        $comment = $conn->real_escape_string($_POST['comment']);
        $shortComment = mb_substr($comment, 0, 200, 'UTF-8');

        $lastDotPos = mb_strrpos($shortComment, '.');
        if ($lastDotPos !== false) {
            $shortComment = mb_substr($shortComment, 0, $lastDotPos + 1);
        }

        $sql_check = "SHOW COLUMNS FROM comment LIKE 'text'";
        $result = $conn->query($sql_check);

        if ($result->num_rows == 0) {
            echo "Ошибка: Поля 'comment' в таблице users не существует!";
            exit();
        }

        $sql = "INSERT INTO comment (text) VALUES ('$shortComment')";

        if ($conn->query($sql) === TRUE) {
            echo "Комментарий успешно сохранён!";
        } else {
            echo "Ошибка: " . $conn->error;
        }
    }
} else {
    echo "Ошибка: Неверный запрос!";
}

$conn->close();
