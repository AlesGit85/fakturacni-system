<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app/Modules/notes/templates/dashboard.latte */
final class Template_9b4fa60bf5 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app/Modules/notes/templates/dashboard.latte';


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		if ($this->global->snippetDriver?->renderSnippets($this->blocks[self::LayerSnippet], $this->params)) {
			return;
		}

		echo '<div class="notes-dashboard">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Poznámky modul</strong> - Jednoduchý systém pro správu poznámek a rychlých zápisků.
            </div>
        </div>
    </div>

    <!-- Hlavní obsah -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background-color: #B1D235; color: #212529;">
                    <i class="bi bi-sticky-fill me-2"></i>
                    <h5 class="mb-0">Moje poznámky</h5>
                </div>
                <div class="card-body">
                    <div class="empty-state-small">
                        <i class="bi bi-sticky text-muted"></i>
                        <p class="mb-0">Zatím nemáte žádné poznámky</p>
                        <small class="text-muted">Klikněte na "Přidat poznámku" pro vytvoření první poznámky</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-2"></i>
                    <h6 class="mb-0">Rychlé akce</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" id="addNoteBtn">
                            <i class="bi bi-plus-circle me-2"></i>
                            Přidat poznámku
                        </button>
                        
                        <button class="btn btn-outline-secondary" id="searchNotesBtn">
                            <i class="bi bi-search me-2"></i>
                            Hledat v poznámkách
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    <h6 class="mb-0">Statistiky</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-number text-primary">0</div>
                            <div class="stat-label">Celkem poznámek</div>
                        </div>
                        <div class="col-6">
                            <div class="stat-number text-success">0</div>
                            <div class="stat-label">Tento týden</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';
	}
}
