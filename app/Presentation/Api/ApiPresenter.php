<?php

declare(strict_types=1);

namespace App\Presentation\Api;

use Nette;

final class ApiPresenter extends Nette\Application\UI\Presenter
{
    /** @var string */
    private $configDir;

    public function __construct()
    {
        parent::__construct();
        $this->configDir = __DIR__ . '/../../../config/fakturaci';
    }

    public function renderOdberatel(): void
    {
        $id = (int) $this->getParameter('id');
        $odberatel = $this->loadOdberatel($id);
        
        $this->sendJson($odberatel ?: ['error' => 'Odběratel nebyl nalezen']);
    }
    
    private function loadOdberatel(int $id): ?array
    {
        $odberateleFile = $this->configDir . '/odberatele.json';
        if (file_exists($odberateleFile)) {
            $odberatele = json_decode(file_get_contents($odberateleFile), true);
            if (is_array($odberatele)) {
                foreach ($odberatele as $odberatel) {
                    if ($odberatel['id'] == $id) {
                        return $odberatel;
                    }
                }
            }
        }
        return null;
    }
}