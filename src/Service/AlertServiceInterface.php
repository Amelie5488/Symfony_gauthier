<?php

namespace App\Service;

interface AlertServiceInterface 
{
    public function success(string $texte): void;
}