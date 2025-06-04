<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app/Modules/test_module/templates/dashboard.latte */
final class Template_9826293dd7 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app/Modules/test_module/templates/dashboard.latte';


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<div class="test-module-dashboard">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background-color: #B1D235; color: #212529;">
                    <i class="bi bi-star-fill me-2"></i>
                    <h5 class="d-inline">Testovací Dashboard</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Úspěch!</strong> Testovací modul byl úspěšně načten a funguje správně.
                    </div>
                    
                    <h6>Informace o modulu:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Název:</strong>
                            <span>Testovací modul</span>
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
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear me-2"></i>
                    <h6 class="d-inline">Funkce modulu</h6>
                </div>
                <div class="card-body">
                    <div class="test-functions">
                        <button id="testButton" class="btn btn-primary btn-sm mb-2 w-100">
                            <i class="bi bi-play-circle"></i> Spustit test
                        </button>
                        
                        <button id="resetButton" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                    
                    <div id="testResult" class="mt-3" style="display: none;">
                        <div class="alert alert-info mb-0">
                            <small>Test byl úspěšně spuštěn!</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-list-check me-2"></i>
                    <h6 class="d-inline">Dostupné funkce</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-check text-success"></i> Základní funkcionalita</li>
                        <li><i class="bi bi-check text-success"></i> Dashboard template</li>
                        <li><i class="bi bi-check text-success"></i> CSS styly</li>
                        <li><i class="bi bi-check text-success"></i> JavaScript funkce</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-code-square me-2"></i>
                    <h6 class="d-inline">Ukázka kódu</h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>// Příklad použití testovacího modulu
class TestModule extends BaseModule {
    public function getName(): string {
        return \'Testovací modul\';
    }
    
    public function getVersion(): string {
        return \'1.0.0\';
    }
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>';
	}
}
