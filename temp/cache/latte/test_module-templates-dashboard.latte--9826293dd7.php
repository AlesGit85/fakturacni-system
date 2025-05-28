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

		echo '<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Úspěch!</strong> Testovací modul funguje správně.
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: #B1D235; color: #212529;">
                <i class="bi bi-info-circle me-2"></i>
                <h5 class="mb-0">Informace o modulu</h5>
            </div>
            <div class="card-body">
                <p><strong>Název:</strong> Testovací modul</p>
                <p><strong>Verze:</strong> 1.0.0</p>
                <p><strong>Autor:</strong> QRdoklad System</p>
                <p class="mb-0"><strong>Popis:</strong> Základní testovací modul pro ověření funkcionality systému</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: #95B11F; color: white;">
                <i class="bi bi-gear me-2"></i>
                <h5 class="mb-0">Funkce modulu</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle text-success me-2"></i> Testování menu integrace</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i> Zobrazení dashboard šablony</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i> Ověření modulu rozhraní</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i> Test aktivace/deaktivace</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background-color: #6c757d; color: white;">
                <i class="bi bi-code-square me-2"></i>
                <h5 class="mb-0">Technické informace</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Cesta k modulu:</h6>
                        <code>app/Modules/test_module/</code>
                    </div>
                    <div class="col-md-4">
                        <h6>Třída modulu:</h6>
                        <code>Modules\\Test_module\\Module</code>
                    </div>
                    <div class="col-md-4">
                        <h6>Šablona:</h6>
                        <code>templates/dashboard.latte</code>
                    </div>
                </div>
                
                <hr>
                
                <h6>Menu položky generované modulem:</h6>
                <ul>
                    <li><strong>Testovací dashboard</strong> - odkaz na detail modulu</li>
                    <li><strong>Test akce 1</strong> - ukázková akce</li>
                    <li><strong>Test akce 2</strong> - další ukázková akce</li>
                </ul>
            </div>
        </div>
    </div>
</div>';
	}
}
