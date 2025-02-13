<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "user_database";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//запись данных в базу данных
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment = $conn->real_escape_string($_POST['comment']);

    $shortComment = mb_substr($comment, 0, 200, 'UTF-8');

    // Ищем последнее полное предложение в пределах 200 символов
    $lastDotPos = mb_strrpos($shortComment, '.');
    if ($lastDotPos !== false) {
        $shortComment = mb_substr($shortComment, 0, $lastDotPos + 1);
    }



    $sql = "INSERT INTO users (comment) VALUES ('$shortComment')";

    if ($conn->query($sql) === TRUE) {
        echo "Данные успешно сохранены!";
    } else {
        echo "Ошибка: " . $conn->error;
    }
}

$conn->close();