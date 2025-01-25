<?php

$jsonFile = 'data.json'; 
$logFile = 'access_log.txt'; 
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Manual refresh by: " . $_SERVER['REMOTE_ADDR'] . PHP_EOL, FILE_APPEND);
}
if (isset($_GET['sse']) && $_GET['sse'] === '1') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    $lastModifiedTime = filemtime($jsonFile);

    while (true) {
        clearstatcache();
        $currentModifiedTime = filemtime($jsonFile);

        if ($currentModifiedTime !== $lastModifiedTime) {
            echo "event: refresh\n";
            echo "data: file_updated\n\n";
            flush();
            break;
        }

        sleep(1);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time JSON Sync</title>
    <script>
        const eventSource = new EventSource('?sse=1');
        eventSource.addEventListener('refresh', function (event) {
            location.reload(); 
        });
    </script>
</head>
<body>
    <h1>Real-Time JSON Sync</h1>
    <p>Monitoring changes to <code>data.json</code>. The page will refresh automatically if the file changes.</p>

    <h2>Current JSON Data:</h2>
    <pre>
<?php
if (file_exists($jsonFile)) {
    echo htmlspecialchars(file_get_contents($jsonFile));
} else {
    echo "JSON file not found!";
}
?>
    </pre>
</body>
</html>
