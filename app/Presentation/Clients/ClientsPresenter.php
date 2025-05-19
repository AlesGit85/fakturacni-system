<?php

namespace App\Presentation\Clients;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ClientsManager;

class ClientsPresenter extends Nette\Application\UI\Presenter
{
    /** @var ClientsManager */
    private $clientsManager;

    public function __construct(ClientsManager $clientsManager)
    {
        $this->clientsManager = $clientsManager;
    }

    public function renderDefault(): void
    {
        $this->template->clients = $this->clientsManager->getAll();
    }

    public function renderShow(int $id): void
    {
        $client = $this->clientsManager->getById($id);

        if (!$client) {
            $this->error('Klient nebyl nalezen');
        }

        $this->template->client = $client;
    }

    protected function createComponentClientForm(): Form
    {
        $form = new Form;

        $form->addText('name', 'Název společnosti:')
            ->setRequired('Zadejte název společnosti');

        $form->addTextArea('address', 'Adresa:')
            ->setRequired('Zadejte adresu');

        $form->addText('city', 'Město:')
            ->setRequired('Zadejte město');

        $form->addText('zip', 'PSČ:')
            ->setRequired('Zadejte PSČ');

        $form->addText('country', 'Země:')
            ->setRequired('Zadejte zemi')
            ->setDefaultValue('Česká republika');

        $form->addText('ic', 'IČ:');
        $form->addText('dic', 'DIČ:');
        $form->addEmail('email', 'E-mail:');
        $form->addText('phone', 'Telefon:');
        $form->addText('bank_account', 'Bankovní účet:');

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = [$this, 'clientFormSucceeded'];

        return $form;
    }

    public function clientFormSucceeded(Form $form, \stdClass $data): void
    {
        $id = $this->getParameter('id');

        if ($id) {
            $this->clientsManager->save($data, $id);
            $this->flashMessage('Klient byl úspěšně aktualizován', 'success');
        } else {
            $this->clientsManager->save($data);
            $this->flashMessage('Klient byl úspěšně přidán', 'success');
        }

        $this->redirect('default');
    }

    public function actionEdit(int $id): void
    {
        $client = $this->clientsManager->getById($id);

        if (!$client) {
            $this->error('Klient nebyl nalezen');
        }

        $this['clientForm']->setDefaults($client);
    }

    public function actionDelete(int $id): void
    {
        $this->clientsManager->delete($id);
        $this->flashMessage('Klient byl úspěšně smazán', 'success');
        $this->redirect('default');
    }
}