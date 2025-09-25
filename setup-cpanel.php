<?php
// Quick setup script for cPanel
// Run this once after uploading to cPanel to switch to cPanel configuration

$files = [
    'bid-request.php',
    'bid-requests.php', 
    'statistics.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace("require_once 'api/config.php';", "require_once 'api/config-cpanel.php';", $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}

echo "Setup complete! Now update your database credentials in api/config-cpanel.php\n";
?>
