<?php

namespace Modules\Core\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Clients\Models\Customer;

class Contacts
{
    private ?Authenticatable $user;

    private Customer $customer;

    public function __construct(Customer $client)
    {
        $this->customer = $client;
        $this->user     = auth()->user();
    }

    public function getSelectedContactsTo()
    {
        return $this->customer->contacts->where('default_to', 1)->pluck('email')->prepend($this->customer->email);
    }

    public function getSelectedContactsCc(): array
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

    public function getSelectedContactsBcc(): array
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

    private function getAllContacts(): array
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

    private function getFormattedContact($name, $email): string
    {
        return $name . ' <' . $email . '>';
    }
}
