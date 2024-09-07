To set up ChatGPT as a bot on Messenger using PHP, you can follow these steps:

Create a Facebook App and Configure Messenger:
1. Log in to Facebook Developer.
2. Create a new app and enable the Messenger product.
3. Configure the webhook and provide the URL that will handle the messages.
4. Obtain the page access token from the Messenger tab in the app settings.

 Set Up the Webhook on the Server:

Create a PHP script to handle the webhook. Here's a simple example:

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

Replace YOUR_PAGE_ACCESS_TOKEN with your Facebook page access token and YOUR_OPENAI_API_KEY with your OpenAI API key.

Host the Webhook and Configure it in Facebook:
1. Upload the PHP script to a publicly accessible web server.
2. Enter the webhook URL in your Facebook app settings and verify it.

Test the Bot:

Send a message to your Facebook page and make sure the bot responds correctly.

By following these steps, you can create a Messenger bot that uses ChatGPT to respond to messages.