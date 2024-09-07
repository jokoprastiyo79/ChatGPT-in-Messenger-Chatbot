<?php
$access_token = 'YOUR_PAGE_ACCESS_TOKEN';

// Verify the token during webhook setup
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_challenge'])) {
        echo $_GET['hub_challenge'];
    }
    exit;
}

// Handle incoming messages
$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['entry'][0]['messaging'][0]['message'])) {
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $message = $input['entry'][0]['messaging'][0]['message']['text'];

    // Send message to ChatGPT API
    $response = file_get_contents("https://api.openai.com/v1/engines/davinci/completions", false, stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer YOUR_OPENAI_API_KEY\r\n" .
                        "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode([
                'prompt' => $message,
                'max_tokens' => 150,
            ]),
        ],
    ]));
    $response = json_decode($response, true);
    $reply = $response['choices'][0]['text'];

    // Send reply back to Messenger
    file_get_contents("https://graph.facebook.com/v12.0/me/messages?access_token=$access_token", false, stream_context_create([
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode([
                'recipient' => ['id' => $sender],
                'message' => ['text' => $reply],
            ]),
        ],
    ]));
}
?>
