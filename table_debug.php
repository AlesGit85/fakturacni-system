<?php
require_once 'vendor/autoload.php';
$bootstrap = new App\Bootstrap;
$container = $bootstrap->bootWebApplication();
$database = $container->getByType(Nette\Database\Explorer::class);

echo "Lokální tabulky:<br>";
$result = $database->query("SHOW TABLES");
foreach ($result as $row) {
    $table = current($row);
    echo "$table<br>";
}
?>