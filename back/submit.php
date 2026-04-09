<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$policy = $_POST['policy'] ?? '';

if ($name === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Введите имя'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Введите корректный email'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$cleanPhone = preg_replace('/[^0-9]/', '', $phone);

// если номер начинается с 8 заменяем на 7
if (strpos($cleanPhone, '8') === 0) {
    $cleanPhone = '7' . substr($cleanPhone, 1);
}

if (mb_strlen($cleanPhone) < 10) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Введите корректный телефон'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$policy) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Подтвердите согласие с политикой конфиденциальности'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $sqlCheck = "
        SELECT id
        FROM requests
        WHERE (name = :name OR email = :email OR phone = :phone)
          AND created_at >= NOW() - INTERVAL '5 minutes'
        LIMIT 1
    ";

    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $cleanPhone
    ]);

    $existingRequest = $stmtCheck->fetch();

    if ($existingRequest) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Заявка с такими данными уже отправлялась в течение последних 5 минут'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sqlInsert = "
        INSERT INTO requests (name, email, phone)
        VALUES (:name, :email, :phone)
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $cleanPhone
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера'
    ], JSON_UNESCAPED_UNICODE);
}