<?php

function formatAddress($object)
{
    if ($object->address || $object->city || $object->state || $object->zip || $object->country) {
        $address = config('ip.addressFormat');

        $address = str_replace('{{ address }}', $object->address, $address);
        $address = str_replace('{{ city }}', $object->city, $address);
        $address = str_replace('{{ state }}', $object->state, $address);
        $address = str_replace('{{ zip }}', $object->zip, $address);
        $address = str_replace('{{ zip_code }}', $object->zip, $address);
        $address = str_replace('{{ postal_code }}', $object->zip, $address);
        $address = str_replace('{{ country }}', $object->country, $address);

        return $address;
    }

    return '';
}
