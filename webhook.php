<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$token = "8238171990:AAH9ZyPSbIyL2xXerFm8keTRZM1qvoaTtmQ";

// قراءة التحديث من Telegram
$update = json_decode(file_get_contents("php://input"), true);

if (!$update) {
    exit("No update");
}

// فقط الرسائل النصية
if (!isset($update["message"]["text"])) {
    exit("No text");
}

$chat_id = $update["message"]["chat"]["id"];
$text = trim($update["message"]["text"]);
$user_id = $update["message"]["from"]["id"];

// قاعدة البيانات
$conn = new mysqli(
    "sql100.ezyro.com",
    "ezyro_40948033",
    "aa98de7b0ad489",
    "ezyro_40948033_telegram_school"
);

if ($conn->connect_error) {
    sendMessage($chat_id, "❌ DB Error: " . $conn->connect_error);
    exit;
}

// تأكد أن مجلد users موجود
if (!is_dir(__DIR__ . "/users")) {
    mkdir(__DIR__ . "/users", 0777, true);
}

// خطوات التسجيل
$step_file = __DIR__ . "/users/{$chat_id}.step";
$step = file_exists($step_file) ? (int)file_get_contents($step_file) : 0;

if ($text === "/start") {
    file_put_contents($step_file, 1);
    sendMessage($chat_id, "مرحبًا 🌟\nاكتب اسمك الكامل");
    exit;
}

if ($step === 1) {
    file_put_contents(__DIR__ . "/users/{$chat_id}_name.txt", $text);
    file_put_contents($step_file, 2);
    sendMessage($chat_id, "اكتب رقم الهاتف");
    exit;
}

if ($step === 2) {
    file_put_contents(__DIR__ . "/users/{$chat_id}_phone.txt", $text);
    file_put_contents($step_file, 3);
    sendMessage($chat_id, "اكتب اسم المدرسة");
    exit;
}

if ($step === 3) {
    file_put_contents(__DIR__ . "/users/{$chat_id}_school.txt", $text);
    file_put_contents($step_file, 4);
    sendMessage($chat_id, "اكتب الصف");
    exit;
}

if ($step === 4) {
    $name   = file_get_contents(__DIR__ . "/users/{$chat_id}_name.txt");
    $phone  = file_get_contents(__DIR__ . "/users/{$chat_id}_phone.txt");
    $school = file_get_contents(__DIR__ . "/users/{$chat_id}_school.txt");

    $stmt = $conn->prepare(
        "INSERT INTO students (telegram_id, full_name, phone, school, class)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $user_id, $name, $phone, $school, $text);
    $stmt->execute();

    // حذف ملفات الخطوات
    unlink($step_file);
    unlink(__DIR__ . "/users/{$chat_id}_name.txt");
    unlink(__DIR__ . "/users/{$chat_id}_phone.txt");
    unlink(__DIR__ . "/users/{$chat_id}_school.txt");

    sendMessage($chat_id, "✅ تم تسجيلك بنجاح");
    exit;
}

// إذا لم يكن في خطوات
sendMessage($chat_id, "اكتب /start للبدء");

function sendMessage($chat_id, $msg) {
    global $token;
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        "chat_id" => $chat_id,
        "text" => $msg
    ];

    $options = [
        "http" => [
            "header"  => "Content-type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
            "timeout" => 10
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}
?>
