<?php
$token = "8238171990:AAH9ZyPSbIyL2xXerFm8keTRZM1qvoaTtmQ";

$update = json_decode(file_get_contents("php://input"), true);
$chat_id = $update["message"]["chat"]["id"];
$text = $update["message"]["text"];
$user_id = $update["message"]["from"]["id"];

$conn = new mysqli("localhost", "root", "", "telegram_school");

$step_file = "users/$chat_id.step";
$step = file_exists($step_file) ? file_get_contents($step_file) : 0;

if ($text == "/start") {
    file_put_contents($step_file, 1);
    sendMessage($chat_id, "مرحبًا 🌟\nاكتب اسمك الكامل");
}

elseif ($step == 1) {
    file_put_contents("users/{$chat_id}_name.txt", $text);
    file_put_contents($step_file, 2);
    sendMessage($chat_id, "اكتب رقم الهاتف");
}

elseif ($step == 2) {
    file_put_contents("users/{$chat_id}_phone.txt", $text);
    file_put_contents($step_file, 3);
    sendMessage($chat_id, "اكتب اسم المدرسة");
}

elseif ($step == 3) {
    file_put_contents("users/{$chat_id}_school.txt", $text);
    file_put_contents($step_file, 4);
    sendMessage($chat_id, "اكتب الصف");
}

elseif ($step == 4) {
    $name = file_get_contents("users/{$chat_id}_name.txt");
    $phone = file_get_contents("users/{$chat_id}_phone.txt");
    $school = file_get_contents("users/{$chat_id}_school.txt");

    $stmt = $conn->prepare(
      "INSERT INTO students (telegram_id, full_name, phone, school, class)
       VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $user_id, $name, $phone, $school, $text);
    $stmt->execute();

    unlink($step_file);
    sendMessage($chat_id, "✅ تم تسجيلك بنجاح");
}

function sendMessage($chat_id, $msg) {
    global $token;
    file_get_contents(
      "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg)
    );
}
?>
