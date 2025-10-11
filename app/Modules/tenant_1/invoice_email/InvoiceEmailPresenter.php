<?php

declare(strict_types=1);

namespace Modules\Tenant1\InvoiceEmail;

use Nette;
use App\Presentation\BasePresenter;
use App\Model\InvoicesManager;
use App\Model\ClientsManager;
use App\Model\CompanyManager;
use App\Model\EmailService;
use App\Model\QrPaymentService;
use TCPDF;

/**
 * Modul pro odesílání faktur emailem
 */
final class InvoiceEmailPresenter extends BasePresenter
{
    /** @var InvoicesManager */
    private $invoicesManager;

    /** @var ClientsManager */
    private $clientsManager;

    /** @var CompanyManager */
    private $companyManager;

    /** @var EmailService */
    private $emailService;

    /** @var QrPaymentService */
    private $qrPaymentService;

    // Pouze účetní a admin mohou odesílat faktury
    protected array $requiredRoles = ['accountant', 'admin'];

    public function __construct(
        InvoicesManager $invoicesManager,
        ClientsManager $clientsManager,
        CompanyManager $companyManager,
        EmailService $emailService,
        QrPaymentService $qrPaymentService
    ) {
        $this->invoicesManager = $invoicesManager;
        $this->clientsManager = $clientsManager;
        $this->companyManager = $companyManager;
        $this->emailService = $emailService;
        $this->qrPaymentService = $qrPaymentService;
    }

    /**
     * Nastavení tenant kontextu
     */
    public function startup(): void
    {
        parent::startup();

        // Nastavíme tenant kontext v manažerech
        $this->invoicesManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );

        $this->clientsManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );

        $this->companyManager->setTenantContext(
            $this->getCurrentTenantId(),
            $this->isSuperAdmin()
        );
    }

    public function renderDefault(): void
    {
        // Prázdná metoda - modul funguje přes handlery
    }

    /**
     * Statická metoda pro hook - vrací HTML tlačítka pro detail faktury
     */
    public static function getInvoiceDetailAction($invoice, $presenter): ?string
    {
        // Kontrola oprávnění - pouze účetní a admin
        $user = $presenter->getUser();
        if (!$user->isInRole('accountant') && !$user->isInRole('admin')) {
            return null;
        }

        // OPRAVENO: Link jde na handler v InvoicesPresenter
        $link = $presenter->link('sendInvoiceEmail!', ['invoiceId' => $invoice->id]);

        return '<a href="' . htmlspecialchars($link) . '" 
        class="btn" 
        style="background-color: #212529; color: #ffffff; border: 1px solid #212529;"
        onmouseover="this.style.backgroundColor=\'#6c757d\'; this.style.borderColor=\'#6c757d\';"
        onmouseout="this.style.backgroundColor=\'#212529\'; this.style.borderColor=\'#212529\';">
        <i class="bi bi-envelope"></i> Odeslat emailem
    </a>';
    }
}
