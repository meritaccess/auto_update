<?php
// Připojení a nastavení
$host = 'localhost';
$db = 'MeritAccessLocal';
$user = 'ma';
$pass = 'FrameWork5414*';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Získání parametru
$mode = isset($_GET['show']) ? $_GET['show'] : '';

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Získání všech tabulek v databázi
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    if ($mode === 'text') {
        header('Content-Type: text/plain; charset=utf-8');

        foreach ($tables as $table) {
            echo "== TABLE: $table ==\n";
            $stmt = $pdo->query("SELECT * FROM `$table`");

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                echo "[EMPTY]\n\n";
                continue;
            }

            // Hlavičky sloupců
            echo implode("\t", array_keys($rows[0])) . "\n";

            // Data řádek po řádku
            foreach ($rows as $row) {
                echo implode("\t", array_map('strval', $row)) . "\n";
            }
            echo "\n";
        }

    } else {
        // Výchozí: XML výstup
        header('Content-Type: application/xml; charset=utf-8');
        $xml = new SimpleXMLElement('<database/>');

        foreach ($tables as $table) {
            $tableElement = $xml->addChild($table);
            $stmt = $pdo->query("SELECT * FROM `$table`");

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rowElement = $tableElement->addChild('row');
                foreach ($row as $key => $value) {
                    $rowElement->addChild($key, htmlspecialchars($value));
                }
            }
        }

        echo $xml->asXML();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . htmlspecialchars($e->getMessage());
    exit;
}
