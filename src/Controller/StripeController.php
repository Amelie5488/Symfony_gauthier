<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\PhotoRepository;
use App\Service\StripeServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StripeController extends AbstractController
{

    public function __construct(
        private StripeServiceInterface $stripeService,
        private EntityManagerInterface $entityManager,
        
    ) {
    }

    #[Route('/stripe', name: 'app_stripe')]
    public function index(Request $request, PhotoRepository $photo,SessionInterface $session): Response
    {
        $panier = $session->get("panier", []);
        $dataPanier = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $img = $photo->find($id);
            $dataPanier[] = [
                "photo" => $img,
                "quantite" => $quantite,

            ];

            $total += $img->getPrix() * $quantite;
        }
        return $this->render('stripe/index.html.twig', [
            "total" => $total
        ]);
    }

    #[Route('/stripe/create-charge/', name: 'app_stripe_charge', methods: ['GET'])]
    public function createCharge(PhotoRepository $photo, EntityManagerInterface $entity, SessionInterface $session)
    {


        $panier = $session->get("panier", []);
        $dataPanier = [];
        $total = 0;

        $order = new Commande();
        $entity->persist($order);


        foreach ($panier as $id => $quantite) {
            $img = $photo->find($id);
            $dataPanier[] = [
                "photo" => $img,
                "quantite" => $quantite,
                "order_id" => $order,

            ];

            $total += $img->getPrix() * $quantite;
        }
        $order->setMontant($total);
        $order->setIsPaid(false);
        $order->setRef($this->stripeService->getSessionId());
        if ($this->getUser()) {
            $order->setUser($this->getUser());
        }
        $entity->flush();
        $url = $this->stripeService->Paiement($dataPanier, $order);
  
        return $this->redirect($url, Response::HTTP_SEE_OTHER);

        $this->addFlash(
            'success',
            'Payment Successful!'
        );
    }

    #[Route('/stripe/success/{order}', name: 'app_stripe_success', methods: ['GET'])]
    public function successOrder($order, SessionInterface $session)
    {
        $panier = $session->get("panier", []);
        $successOrder =  $this->entityManager->find(Commande::class, $order);
        $successOrder->setIsPaid(true);
        $this->entityManager->flush();
        $session->set("panier", []);
        return $this->render('stripe/success.html.twig');
    }
    #[Route('/stripe/cancel/{order}', name: 'app_stripe_cancel', methods: ['GET'])]
    public function cancelOrder(int $order)
    {
        $cancelOrder = $this->entityManager->find(Commande::class, $order);
        if($cancelOrder === null){
            throw new \RuntimeException('Pas de commande bichon');
        }
        if ($cancelOrder->isIsPaid()) {
            throw new \RuntimeException('Non la commande est payé et ne peut être supprimé');
        }
        $this->entityManager->remove($cancelOrder);
        $this->entityManager->flush();

        return $this->render('stripe/success.html.twig');
    }
}
