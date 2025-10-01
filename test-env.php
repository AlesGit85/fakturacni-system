<?php
echo "SMTP_USERNAME: " . ($_ENV['SMTP_USERNAME'] ?? 'NENALEZENO') . "\n";
echo "SMTP_PASSWORD: " . (empty($_ENV['SMTP_PASSWORD']) ? 'PRÁZDNÉ' : 'NASTAVENO') . "\n";
echo "getenv SMTP_USERNAME: " . (getenv('SMTP_USERNAME') ?: 'NENALEZENO') . "\n";
echo "getenv SMTP_PASSWORD: " . (empty(getenv('SMTP_PASSWORD')) ? 'PRÁZDNÉ' : 'NASTAVENO') . "\n";
?>