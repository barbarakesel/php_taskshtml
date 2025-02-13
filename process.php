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
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $email = $conn->real_escape_string($_POST['email']);

// Регулярное выражение для проверки email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Ошибка: Некорректный формат email!";
        exit();
    }
    
    $sql = "INSERT INTO users (first_name, last_name, email) VALUES ('$firstName', '$lastName', '$email')";

    if ($conn->query($sql) === TRUE) {
        echo "Данные успешно сохранены!";
    } else {
        echo "Ошибка: " . $conn->error;
    }
}

$conn->close();