<?php

namespace App\Support;

use App\IpModules\Customers\Models\Customer;
use Collective\Html\FormFacade;

class Contacts
{
    private $client;

    private $user;

    public function __construct(Customer $client)
    {
        $this->customer = $client;
        $this->user     = auth()->user();
    }

    public function contactDropdownTo()
    {
        $allContacts      = $this->getAllContacts();
        $selectedContacts = $this->getSelectedContactsTo();

        return FormFacade::select('to', $allContacts, $selectedContacts, ['id' => 'to', 'multiple' => 'multiple', 'class' => 'form-control']);
    }

    public function getSelectedContactsTo()
    {
        return $this->customer->contacts->where('default_to', 1)->pluck('email')->prepend($this->customer->email);
    }

    public function contactDropdownCc()
    {
        $allContacts      = $this->getAllContacts();
        $selectedContacts = $this->getSelectedContactsCc();

        return FormFacade::select('cc', $allContacts, $selectedContacts, ['id' => 'cc', 'multiple' => 'multiple', 'class' => 'form-control']);
    }

    public function getSelectedContactsCc()
    {
        $contacts = $this->customer->contacts
            ->where('default_cc', 1)
            ->pluck('email')
            ->toArray();

        if (config('ip.mailDefaultCc')) {
            $contacts = array_merge($contacts, [config('ip.mailDefaultCc')]);
        }

        return $contacts;
    }

    public function contactDropdownBcc()
    {
        $allContacts      = $this->getAllContacts();
        $selectedContacts = $this->getSelectedContactsBcc();

        return FormFacade::select('bcc', $allContacts, $selectedContacts, ['id' => 'bcc', 'multiple' => 'multiple', 'class' => 'form-control']);
    }

    public function getSelectedContactsBcc()
    {
        $contacts = $this->customer->contacts
            ->where('default_bcc', 1)
            ->pluck('email')
            ->toArray();

        if (config('ip.mailDefaultBcc')) {
            $contacts = array_merge($contacts, [config('ip.mailDefaultBcc')]);
        }

        return $contacts;
    }

    private function getAllContacts()
    {
        $contacts = ($this->customer->email) ? [$this->customer->email => $this->getFormattedContact($this->customer->name, $this->customer->email)] : [];

        foreach ($this->customer->contacts->pluck('name', 'email') as $email => $name) {
            $contacts[$email] = $this->getFormattedContact($name, $email);
        }

        $contacts[$this->user->email] = $this->getFormattedContact($this->user->name, $this->user->email);

        if (config('ip.mailDefaultCc')) {
            $contacts[config('ip.mailDefaultCc')] = config('ip.mailDefaultCc');
        }

        if (config('ip.mailDefaultBcc')) {
            $contacts[config('ip.mailDefaultBcc')] = config('ip.mailDefaultBcc');
        }

        return $contacts;
    }

    private function getFormattedContact($name, $email)
    {
        return $name . ' <' . $email . '>';
    }
}
