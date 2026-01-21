<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$token = "8238171990:AAH9ZyPSbIyL2xXerFm8keTRZM1qvoaTtmQ";

$update = json_decode(file_get_contents("php://input"), true);

if (!$update) {
    exit("No update");
}

$chat_id = $update["message"]["chat"]["id"] ?? null;
$text = $update["message"]["text"] ?? null;

if (!$chat_id) {
    exit("No chat id");
}

if (!$text) {
    sendMessage($chat_id, "📌 فقط الرسائل النصية مسموحة");
    exit;
}

sendMessage($chat_id, "تم استلام رسالتك: " . $text);

function sendMessage($chat_id, $msg) {
    global $token;
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $data = [
        "chat_id" => $chat_id,
        "text" => $msg
    ];

    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($data),
            "timeout" => 10
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // Save log
    file_put_contents(__DIR__ . "/log.txt", date("Y-m-d H:i:s") . " => " . $result . PHP_EOL, FILE_APPEND);

    return $result;
}
?>
