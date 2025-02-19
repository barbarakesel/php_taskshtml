<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "user_database";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function notify($message) {
    echo "<div class='alert'>$message</div>";
}

$success = ""; // Сообщение об успешном действии

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
    } elseif ($form_type == "form3") {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $email = isset($_POST['email']) ? trim($_POST['email']) : null;
            if (empty($email)) {
                $errors[] = 'Введите email';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Неверный email';
            }

            $password = isset($_POST['password']) ? trim($_POST['password']) : null;
            if (empty($password)) {
                $errors[] = 'Введите пароль';
            } elseif (strlen($password) < 6 || strlen($password) > 50) {
                $errors[] = 'Пароль должен содержать от 6 до 50 символов';
            }

            $passwordRepeat = isset($_POST['password_repeat']) ? trim($_POST['password_repeat']) : null;
            if ($password !== $passwordRepeat) {
                $errors[] = 'Пароль подтвержден неверно';
            }

            if (empty($errors)) {
                try {
                    // Проверяем соединение
                    if (!$conn) {
                        die("Ошибка соединения с БД: " . mysqli_connect_error());
                    }

                    // Проверяем, существует ли пользователь
                    $stmt = $conn->prepare("SELECT user_id FROM authorization WHERE login = ?");
                    if (!$stmt) {
                        die("Ошибка подготовки запроса: " . $conn->error);
                    }

                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->fetch_assoc()) {
                        echo 'Пользователь с таким email уже существует';
                    } else {
                        $stmt = $conn->prepare("INSERT INTO authorization (login, password) VALUES (?, ?)");
                        if (!$stmt) {
                            die("Ошибка подготовки запроса: " . $conn->error);
                        }

                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt->bind_param("ss", $email, $passwordHash);
                        $stmt->execute();

                        echo 'Регистрация успешна!';
                    }
                } catch (mysqli_sql_exception $e) {
                    echo 'Произошла ошибка при регистрации';
                    error_log($e->getMessage());
                }
            } else {
                echo implode('<br>', $errors);
            }
        }
    } elseif ($form_type == "form4") {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            // Получаем логин и пароль из формы
            $email = isset($_POST['email']) ? trim($_POST['email']) : null;
            if (empty($email)) {
                $errors[] = 'Введите email';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Неверный email';
            }

            $password = isset($_POST['password']) ? trim($_POST['password']) : null;
            if (empty($password)) {
                $errors[] = 'Введите пароль';
            }

            if (empty($errors)) {
                try {
                    if (!$conn) {
                        die("Ошибка соединения с БД: " . mysqli_connect_error());
                    }

                    $stmt = $conn->prepare("SELECT user_id, password FROM authorization WHERE login = ?");
                    if (!$stmt) {
                        die("Ошибка подготовки запроса: " . $conn->error);
                    }

                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($user = $result->fetch_assoc()) {
                        
                        if (password_verify($password, $user['password'])) {
                            echo 'Авторизация успешна!';
                        } else {
                            echo 'Неверный пароль';
                        }
                    }
                    else {
                        echo 'Пользователь с таким email не существует';
                    }
                } catch (mysqli_sql_exception $e) {
                    echo 'Произошла ошибка при авторизации';
                    error_log($e->getMessage());
                }
            } else {
                // Выводим ошибки
                echo implode('<br>', $errors);
            }
        }
    }
} else {
    echo "Ошибка: Неверный запрос!";
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
