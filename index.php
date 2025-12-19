<?php
// .envから設定を読み込む（簡易実装）
$env = parse_ini_file('.env');
$api_key = $env['OPENAI_API_KEY'] ?? '';

$result = "";
$input_text = $_POST['query'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($input_text)) {
    $url = "https://api.openai.com/v1/chat/completions";

    $data = [
        "model" => "gpt-5-mini", // 指定のモデル名
        "messages" => [
            [
                "role" => "system",
                "content" => "与えられた文章から最も重要な単語を1つだけ選び、『日本語：英語』の形式で出力してください。余計な説明は一切不要です。"
            ],
            [
                "role" => "user",
                "content" => $input_text
            ]
        ],
        "temperature" => 0.3
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ]);

    $response = curl_exec($ch);
    $response_data = json_decode($response, true);
    curl_close($ch);

    $result = $response_data['choices'][0]['message']['content'] ?? "エラーが発生しました。";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>AI Word Extractor</title>
</head>
<body>
    <h1>重要語句チェッカー</h1>
    <form method="POST">
        <input type="text" name="query" placeholder="文章を入力してください" style="width: 300px;" required>
        <button type="submit">送信</button>
    </form>

    <?php if ($result): ?>
        <h2>結果:</h2>
        <pre><?php echo htmlspecialchars($result); ?></pre>
    <?php endif; ?>
</body>
</html>