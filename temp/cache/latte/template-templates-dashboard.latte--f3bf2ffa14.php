<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app/Modules/template/templates/dashboard.latte */
final class Template_f3bf2ffa14 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app/Modules/template/templates/dashboard.latte';


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<div class="template-module-dashboard">
    <!-- Úvodní informace -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Template Modul Dashboard</strong> - Toto je základní template pro vytváření nových modulů. 
                Upravte tento soubor podle vašich potřeb.
            </div>
        </div>
    </div>

    <!-- Informační karty -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="template-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-star-fill" style="color: #B1D235;"></i>
                </div>
                <div class="stat-number" id="templateStat1">0</div>
                <div class="stat-label">Template Statistika 1</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="template-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-gear-fill" style="color: #95B11F;"></i>
                </div>
                <div class="stat-number" id="templateStat2">0</div>
                <div class="stat-label">Template Statistika 2</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="template-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-graph-up" style="color: #6c757d;"></i>
                </div>
                <div class="stat-number" id="templateStat3">0</div>
                <div class="stat-label">Template Statistika 3</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="template-stat-card text-center">
                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill" style="color: #28a745;"></i>
                </div>
                <div class="stat-number" id="templateStat4">0</div>
                <div class="stat-label">Template Statistika 4</div>
            </div>
        </div>
    </div>

    <!-- Hlavní obsah -->
    <div class="row g-4">
        <!-- Levý sloupec - Akce a funkce -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #B1D235; color: #212529;">
                    <i class="bi bi-lightning-fill me-2"></i>
                    <h5 class="mb-0">Template Funkce</h5>
                </div>
                <div class="card-body">
                    <!-- Tlačítka pro AJAX akce -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary w-100" id="loadTestDataBtn">
                                <i class="bi bi-database"></i> Načíst testovací data
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary w-100" id="loadModuleInfoBtn">
                                <i class="bi bi-info-circle"></i> Informace o modulu
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success w-100" id="loadStatsBtn">
                                <i class="bi bi-graph-up"></i> Načíst statistiky
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-success w-100" id="resetDataBtn">
                                <i class="bi bi-arrow-clockwise"></i> Reset dat
                            </button>
                        </div>
                    </div>

                    <!-- Výsledek AJAX volání -->
                    <div id="ajaxResult" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <strong>Výsledek:</strong>
                            <pre id="ajaxResultContent" class="mt-2 mb-0"></pre>
                        </div>
                    </div>

                    <!-- Loading indikátor -->
                    <div id="loadingIndicator" class="text-center mt-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Načítání...</span>
                        </div>
                        <p class="mt-2 mb-0">Zpracovávám požadavek...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pravý sloupec - Informace a konfigurace -->
        <div class="col-md-4">
            <!-- Informace o modulu -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header" style="background-color: #95B11F; color: white;">
                    <i class="bi bi-info-circle me-2"></i>
                    <h5 class="mb-0">Informace o modulu</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" id="moduleInfoList">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Název:</strong>
                            <span>Template Modul</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Verze:</strong>
                            <span>1.0.0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Autor:</strong>
                            <span>QRdoklad System</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Stav:</strong>
                            <span class="badge bg-success">Aktivní</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Rychlé akce -->
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #6c757d; color: white;">
                    <i class="bi bi-tools me-2"></i>
                    <h5 class="mb-0">Rychlé akce</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="exportDataBtn">
                            <i class="bi bi-download"></i> Export dat
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearCacheBtn">
                            <i class="bi bi-trash"></i> Vymazat cache
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" id="debugModeBtn">
                            <i class="bi bi-bug"></i> Debug režim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ukázkový formulář -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background-color: #f8f9fa; color: #212529;">
                    <i class="bi bi-form me-2"></i>
                    <h5 class="mb-0">Ukázkový formulář</h5>
                </div>
                <div class="card-body">
                    <form id="templateForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="templateInput1" class="form-label">Textové pole</label>
                                <input type="text" class="form-control" id="templateInput1" name="input1" placeholder="Zadejte text...">
                            </div>
                            <div class="col-md-6">
                                <label for="templateSelect1" class="form-label">Výběr</label>
                                <select class="form-select" id="templateSelect1" name="select1">
                                    <option value="">Vyberte možnost...</option>
                                    <option value="option1">Možnost 1</option>
                                    <option value="option2">Možnost 2</option>
                                    <option value="option3">Možnost 3</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="templateNumber1" class="form-label">Číslo</label>
                                <input type="number" class="form-control" id="templateNumber1" name="number1" placeholder="0">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="templateCheck1" name="check1">
                                    <label class="form-check-label" for="templateCheck1">
                                        Povolit funkci
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="templateTextarea1" class="form-label">Dlouhý text</label>
                                <textarea class="form-control" id="templateTextarea1" name="textarea1" rows="3" placeholder="Zadejte delší text..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Odeslat formulář
                                </button>
                                <button type="reset" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-x-lg"></i> Vymazat
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Poznámky pro vývojáře -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Pro vývojáře:</strong> Tento template obsahuje příklady všech základních funkcí. 
                Odstraňte nepotřebné sekce a přidejte vlastní funkcionalitu podle potřeb vašeho modulu.
            </div>
        </div>
    </div>
</div>

';
	}
}
