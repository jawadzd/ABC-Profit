<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $originalUrl = $_POST['url'];

    // Validate URL format
    if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
        echo "Invalid URL format.";
        exit;
    }

    // Check if the URL already exists
    $stmt = $pdo->prepare("SELECT short_url FROM urls WHERE original_url = :url");
    $stmt->execute(['url' => $originalUrl]);
    $existingShortUrl = $stmt->fetchColumn();

    if ($existingShortUrl) {
        echo "Короткий URL-адрес уже существует: http://localhost/code/$existingShortUrl";
    } else {
        // Generate a unique short URL
        $shortUrl = generateShortUrl();

        // Insert the new mapping
        $stmt = $pdo->prepare("INSERT INTO urls (original_url, short_url) VALUES (:original_url, :short_url)");
        $stmt->execute(['original_url' => $originalUrl, 'short_url' => $shortUrl]);

        echo "Короткий URL-адрес создан: http://localhost/code/$shortUrl";
    }
}

// Function to generate a random short URL
function generateShortUrl() {
    global $pdo;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $length = 6;

    do {
        $shortUrl = '';
        for ($i = 0; $i < $length; $i++) {
            $shortUrl .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Check if the short URL already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM urls WHERE short_url = :short_url");
        $stmt->execute(['short_url' => $shortUrl]);
        $exists = $stmt->fetchColumn();
    } while ($exists);

    return $shortUrl;
}
?>

<?php
// Redirection logic
include 'config.php';

if (isset($_GET['short_url'])) {
    $shortUrl = $_GET['short_url'];

    // Fetch the original URL
    $stmt = $pdo->prepare("SELECT original_url FROM urls WHERE short_url = :short_url");
    $stmt->execute(['short_url' => $shortUrl]);
    $originalUrl = $stmt->fetchColumn();

    if ($originalUrl) {
        header("Location: $originalUrl");
        exit;
    } else {
        echo "Короткий URL-адрес не найден!";
    }
}
?>
