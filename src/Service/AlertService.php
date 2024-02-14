<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AlertService implements AlertServiceInterface {

private const ALERT_SUCCESS = "success";
private const ALERT_DANGER = "danger";
private const ALERT_WARNING = "warning";
private const ALERT_INFOS = "info";

private Session $session;

    public function __construct()
    {

$this->session =new Session();
        
    }

    public function success(string $texte): void
    {
         $this->session->getFlashBag()->add(self::ALERT_SUCCESS, $texte);
    }
}