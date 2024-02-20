<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StripeService implements StripeServiceInterface
{

    private const STRIPE_PAYMENT_ID = 'session_stripe_payment_id';
    private const STRIPE_PAYMENT_ORDER = 'session_stripe_payment_order';

    public function __construct(
        readonly private string $stripeSecret,
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private RequestStack $requestStack,
    ) {
        Stripe::setApiKey($stripeSecret);
    }

    public function Paiement($panier, $id_order): string
    {
        $mySession = $this->requestStack->getSession();
        $session = Session::create([
            'success_url' => $this->urlGenerator->generate('app_stripe_success', ['order' => $id_order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'=>  $this->urlGenerator->generate('app_stripe_cancel', ['order' => $id_order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    $this->getLineItems($panier),
                ]
            ],
            'mode' => 'payment',
        ]);

        $mySession->set(self::STRIPE_PAYMENT_ID, $session->id);
        $mySession->set(self::STRIPE_PAYMENT_ORDER, $id_order->getId());

        return $session->url;
    }

    public function getSessionId(): mixed
    {
        return $this->requestStack->getSession()->get(self::STRIPE_PAYMENT_ID);
    }
    public function getSessionOrder(): mixed
    {
        return $this->requestStack->getSession()->get(self::STRIPE_PAYMENT_ORDER);
    }


    private function getLineItems($orderItems): array
    {
        $products = [];

        /** @var OrderItem $item */
        foreach ($orderItems as $item) {
            $product['price_data']['currency'] = 'eur';
            $product['price_data']['product_data']['name'] = $item['photo']->getNom();
            $product['price_data']['unit_amount'] = $item['photo']->getprix() * 100;
            $product['quantity'] = $item['quantite'];

            $products[] = $product;
        }
        return $products;
    }

}
