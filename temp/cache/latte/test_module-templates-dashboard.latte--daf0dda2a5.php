<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app/Modules/test_module/templates/dashboard.latte */
final class Template_daf0dda2a5 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app/Modules/test_module/templates/dashboard.latte';

	public const Blocks = [
		['content' => 'blockContent'],
	];


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		$this->renderBlock('content', get_defined_vars()) /* line 1 */;
	}


	/** {block content} on line 1 */
	public function blockContent(array $ʟ_args): void
	{
		echo '<div class="test-module-dashboard">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0" style="background: linear-gradient(135deg, #B1D235 0%, #95B11F 100%);">
                    <div class="card-body text-white p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-3" style="color: white; font-weight: 600;">
                                    <i class="bi bi-trophy-fill me-3"></i>
                                    Gratulujeme! Modul byl úspěšně nainstalován
                                </h4>
                                <p class="lead mb-0" style="color: #212529;">
                                    Právě jste si vyzkoušeli, jak jednoduché je rozšířit náš systém novými funkcemi. 
                                    Instalace trvala jen pár sekund!
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="bi bi-puzzle" style="font-size: 4rem; color: #212529; opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #f8f9fa; border-bottom: 2px solid #B1D235;">
                        <h5 class="mb-0" style="color: #212529;">
                            <i class="bi bi-gear-fill me-2" style="color: #B1D235;"></i>
                            Co se právě stalo?
                        </h5>
                        <span class="badge" style="background-color: #B1D235; color: #212529;">TESTOVACÍ MODUL</span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3" style="color: #212529;">
                                    <i class="bi bi-list-check me-2" style="color: #95B11F;"></i>
                                    Kroky instalace modulu:
                                </h6>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex align-items-center border-0 py-2">
                                        <i class="bi bi-check-circle-fill me-3" style="color: #B1D235; font-size: 1.2rem;"></i>
                                        <div>
                                            <strong>1. Nahrání souboru</strong>
                                            <br><small style="color: #6c757d;">Modul byl nahrán ze ZIP souboru</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex align-items-center border-0 py-2">
                                        <i class="bi bi-check-circle-fill me-3" style="color: #B1D235; font-size: 1.2rem;"></i>
                                        <div>
                                            <strong>2. Automatická instalace</strong>
                                            <br><small style="color: #6c757d;">Soubory byly rozbaleny a zkonfigurovány</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex align-items-center border-0 py-2">
                                        <i class="bi bi-check-circle-fill me-3" style="color: #B1D235; font-size: 1.2rem;"></i>
                                        <div>
                                            <strong>3. Aktivace modulu</strong>
                                            <br><small style="color: #6c757d;">Modul byl aktivován a přidán do menu</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex align-items-center border-0 py-2">
                                        <i class="bi bi-check-circle-fill me-3" style="color: #B1D235; font-size: 1.2rem;"></i>
                                        <div>
                                            <strong>4. Připraven k použití</strong>
                                            <br><small style="color: #6c757d;">Modul je funkční a dostupný v levém menu</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success border-0 mb-4" style="background-color: rgba(177, 210, 53, 0.1); border-left: 4px solid #B1D235 !important;">
                            <h6 style="color: #95B11F;">
                                <i class="bi bi-lightbulb-fill me-2"></i>
                                Testovací funkce modulu
                            </h6>
                            <p class="mb-2" style="color: #6c757d;">
                                Tento modul obsahuje několik demo funkcí, které ukazují možnosti našeho modulového systému:
                            </p>
                            <ul class="mb-0" style="color: #6c757d;">
                                <li>Vlastní dashboard (tato stránka)</li>
                                <li>CSS styly a JavaScript funkce</li>
                                <li>Integrace do hlavního menu</li>
                                <li>Konfigurace a nastavení</li>
                            </ul>
                        </div>

                        <h6 class="mb-3" style="color: #212529;">📦 Detail nainstalovaného modulu:</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="color: #6c757d; width: 30%;">Název modulu:</td>
                                        <td>Testovací modul</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold" style="color: #6c757d;">Verze:</td>
                                        <td>
                                            <span class="badge bg-light text-dark">1.4.2</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold" style="color: #6c757d;">Typ modulu:</td>
                                        <td>Demo/Testovací</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold" style="color: #6c757d;">Velikost:</td>
                                        <td>~15 KB</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold" style="color: #6c757d;">Stav:</td>
                                        <td>
                                            <span class="badge" style="background-color: #B1D235; color: #212529;">
                                                <i class="bi bi-check-circle me-1"></i>Aktivní a funkční
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header" style="background-color: #95B11F; color: white;">
                        <h6 class="mb-0">
                            <i class="bi bi-tools me-2"></i>
                            Akce s modulem
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="test-functions d-grid gap-2">
                            <button id="testButton" class="btn btn-primary btn-sm" style="background-color: #B1D235; border-color: #B1D235; color: #212529;">
                                <i class="bi bi-play-circle me-1"></i>
                                Test funkcí modulu
                            </button>
                            
                            <button class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pause-circle me-1"></i>
                                Deaktivovat modul
                            </button>
                            
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>
                                Odinstalovat modul
                            </button>
                            
                            <hr class="my-3">
                            
                            <div class="text-center mb-3">
                                <small style="color: #6c757d;">Správa modulů:</small>
                            </div>
                            
                            <button class="btn btn-outline-success btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>
                                Instalovat další modul
                            </button>
                            
                            <button class="btn btn-outline-info btn-sm">
                                <i class="bi bi-list me-1"></i>
                                Všechny moduly
                            </button>
                            
                            <button class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-shop me-1"></i>
                                Obchod s moduly
                            </button>
                        </div>
                        
                        <div id="testResult" class="mt-3" style="display: none;">
                        </div>
                        
                        <div class="mt-4 p-3 rounded" style="background-color: rgba(177, 210, 53, 0.1); border: 1px solid #B1D235;">
                            <h6 style="color: #95B11F;">
                                <i class="bi bi-info-circle me-2"></i>
                                Víte, že...?
                            </h6>
                            <small style="color: #6c757d;">
                                Instalace modulu trvala méně než 5 sekund! Náš systém podporuje 
                                desítky různých modulů pro rozšíření funkcionalit.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0" style="background: linear-gradient(45deg, #B1D235, #95B11F);">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-download" style="font-size: 2.5rem; color: #212529; margin-bottom: 1rem;"></i>
                        <h5 style="color: #212529;">Stáhnout další moduly</h5>
                        <p class="mb-3" style="color: #212529; opacity: 0.8;">
                            Prozkoumejte naši knihovnu modulů a rozšiřte funkčnost systému
                        </p>
                        <button class="btn btn-light">
                            <i class="bi bi-shop me-1"></i>
                            Procházet moduly
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0" style="background: linear-gradient(45deg, #6c757d, #212529);">
                    <div class="card-body text-center text-white p-4">
                        <i class="bi bi-code-slash" style="font-size: 2.5rem; margin-bottom: 1rem;"></i>
                        <h5>Vytvořit vlastní modul</h5>
                        <p class="mb-3" style="opacity: 0.8;">
                            Naučte se vytvářet vlastní moduly podle naší dokumentace
                        </p>
                        <button class="btn btn-outline-light">
                            <i class="bi bi-book me-1"></i>
                            Dokumentace
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';
	}
}
