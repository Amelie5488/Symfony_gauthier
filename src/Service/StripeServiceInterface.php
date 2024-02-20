<?php

namespace App\Service;

interface StripeServiceInterface
{
    public function Paiement($order,  $id_order): string;

    public function getSessionId(): mixed;

    public function getSessionOrder(): mixed;
    
}