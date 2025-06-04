<?php

declare(strict_types=1);

use Latte\Runtime as LR;

/** source: D:\_coding\nette\fakturacni-system\app\Presentation\Users/profile.latte */
final class Template_44569c9011 extends Latte\Runtime\Template
{
	public const Source = 'D:\\_coding\\nette\\fakturacni-system\\app\\Presentation\\Users/profile.latte';

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
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div class="user-form-container">
    <div class="page-header">
        <h1 class="main-title">Můj profil</h1>
        <p class="text-muted">Správa vašeho uživatelského účtu</p>
    </div>

    <!-- Aktuální informace o profilu -->
    <div class="info-card mb-4">
        <div class="info-card-header">
            <i class="bi bi-person-circle me-2"></i>
            <h3>Aktuální údaje</h3>
        </div>
        <div class="info-card-body">
            <div class="row">
                <div class="col-md-6">
';
		if ($profileUser->first_name || $profileUser->last_name) /* line 17 */ {
			echo '                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person"></i>
                            Jméno
                        </div>
                        <div class="info-value">
                            ';
			if ($profileUser->first_name) /* line 24 */ {
				echo LR\Filters::escapeHtmlText($profileUser->first_name) /* line 24 */;
			}
			echo '
                            ';
			if ($profileUser->last_name) /* line 25 */ {
				echo ' ';
				echo LR\Filters::escapeHtmlText($profileUser->last_name) /* line 25 */;
			}
			echo '
                        </div>
                    </div>
';
		}
		echo '                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-at"></i>
                            Uživatelské jméno
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText($profileUser->username) /* line 35 */;
		echo '</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-envelope"></i>
                            E-mail
                        </div>
                        <div class="info-value">';
		echo LR\Filters::escapeHtmlText($profileUser->email) /* line 43 */;
		echo '</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-shield"></i>
                            Role
                        </div>
                        <div class="info-value">
';
		if ($profileUser->role === 'admin') /* line 53 */ {
			echo '                                <span class="badge bg-danger">Administrátor</span>
';
		} elseif ($profileUser->role === 'accountant') /* line 55 */ {
			echo '                                <span class="badge bg-warning text-dark">Účetní</span>
';
		} else /* line 57 */ {
			echo '                                <span class="badge bg-secondary">Pouze čtení</span>
';
		}

		echo '                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-calendar"></i>
                            Registrován
                        </div>
                        <div class="info-value">
';
		if ($profileUser->created_at) /* line 69 */ {
			echo '                                ';
			echo LR\Filters::escapeHtmlText(($this->filters->date)($profileUser->created_at, 'd.m.Y')) /* line 70 */;
			echo "\n";
		} else /* line 71 */ {
			echo '                                <span class="text-muted">—</span>
';
		}
		echo '                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulář pro úpravu profilu -->
    <div class="card shadow-sm rounded-lg border-0">
        <div class="card-header">
            <i class="bi bi-pencil-square me-2"></i>
            <h3>Upravit profil</h3>
        </div>
        <div class="card-body p-4">
            ';
		$form = $this->global->formsStack[] = $this->global->uiControl['profileForm'] /* line 88 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['class' => 'row g-4']) /* line 88 */;
		echo '
                <!-- Osobní údaje -->
                <div class="col-12">
                    <div class="section-header">
                        <i class="bi bi-person-vcard"></i>
                        <h2 class="section-title">Osobní údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('first_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 99 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('first_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 100 */;
		echo "\n";
		if ($form['first_name']->hasErrors()) /* line 101 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['first_name']->getError()) /* line 102 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('last_name', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 109 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('last_name', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 110 */;
		echo "\n";
		if ($form['last_name']->hasErrors()) /* line 111 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['last_name']->getError()) /* line 112 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <!-- Přihlašovací údaje -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-at"></i>
                        <h2 class="section-title">Přihlašovací údaje</h2>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 127 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 128 */;
		echo "\n";
		if ($form['username']->hasErrors()) /* line 129 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['username']->getError()) /* line 130 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 137 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('email', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 138 */;
		echo "\n";
		if ($form['email']->hasErrors()) /* line 139 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['email']->getError()) /* line 140 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <!-- Změna hesla -->
                <div class="col-12 mt-4">
                    <div class="section-header">
                        <i class="bi bi-key"></i>
                        <h2 class="section-title">Změna hesla</h2>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Pro změnu hesla vyplňte všechna tři pole. Heslo musí mít alespoň 6 znaků. Pokud nechcete měnit heslo, ponechte je prázdná.
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('currentPassword', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 159 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('currentPassword', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 160 */;
		echo "\n";
		if ($form['currentPassword']->hasErrors()) /* line 161 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['currentPassword']->getError()) /* line 162 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 169 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('password', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 170 */;
		echo "\n";
		if ($form['password']->hasErrors()) /* line 171 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['password']->getError()) /* line 172 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-floating">
                        ';
		echo Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getControl()->addAttributes(['class' => 'form-control']) /* line 179 */;
		echo '
                        ';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('passwordVerify', $this->global)->getLabel())?->addAttributes(['class' => 'form-label']) /* line 180 */;
		echo "\n";
		if ($form['passwordVerify']->hasErrors()) /* line 181 */ {
			echo '                            <div class="text-danger mt-1">';
			echo LR\Filters::escapeHtmlText($form['passwordVerify']->getError()) /* line 182 */;
			echo '</div>
';
		}
		echo '                    </div>
                </div>
                
';
		if ($form->hasErrors()) /* line 187 */ {
			echo '                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Prosím opravte chyby ve formuláři.
                        </div>
                    </div>
';
		}
		echo '                
                <div class="col-12 mt-4 d-flex justify-content-between">
                    <a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 197 */;
		echo '" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Zpět na uživatele
                    </a>
                    <button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line 200 */;
		echo ' class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Uložit změny
                    </button>
                </div>
            ';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line 204 */;

		echo '
        </div>
    </div>
</div>
';
	}
}
