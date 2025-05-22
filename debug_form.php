<?php
// Debug skript pro sledování odesílání formulářů
// Přidejte tento kód na začátek metody profileFormSucceeded v UsersPresenter.php

public function profileFormSucceeded(Form $form, \stdClass $data): void
{
    // DEBUG - zalogujeme všechna přijatá data
    file_put_contents(__DIR__ . '/../../../temp/debug_form.log', 
        date('Y-m-d H:i:s') . " - Formulář odeslán\n" .
        "Data: " . print_r($data, true) . "\n" .
        "UserID: " . $this->getUser()->getId() . "\n" .
        str_repeat("-", 50) . "\n", 
        FILE_APPEND
    );
    
    $userId = $this->getUser()->getId();
    
    try {
        // ... zbytek kódu zůstává stejný
    } catch (\Exception $e) {
        // DEBUG - zalogujeme chybu
        file_put_contents(__DIR__ . '/../../../temp/debug_form.log', 
            date('Y-m-d H:i:s') . " - CHYBA: " . $e->getMessage() . "\n" .
            "Stack trace: " . $e->getTraceAsString() . "\n" .
            str_repeat("=", 50) . "\n", 
            FILE_APPEND
        );
        
        $form->addError('Při úpravě profilu došlo k chybě: ' . $e->getMessage());
    }
}
?>