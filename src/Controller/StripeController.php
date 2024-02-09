<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(SessionInterface $session,Request $request, PhotoRepository $photo,): Response
    {
        $panier = $session->get("panier", []);
        $dataPanier = [];
        $total = 0 ;

        foreach($panier as $id => $quantite){
            $img = $photo->find($id);
            $dataPanier[] = [
                "photo"=>$img,
                "quantite"=>$quantite,
          
            ];

            $total += $img->getPrix() * $quantite;
        }
        return $this->render('stripe/index.html.twig', [
            'stripe_key' => $_ENV["STRIPE_KEY"],
            "total"=>$total
        ]);
    }

    #[Route('/stripe/create-charge/', name: 'app_stripe_charge', methods:  ['POST'])]
    public function createCharge(Request $request, PhotoRepository $photo, SessionInterface $session, EntityManagerInterface $entity)
    {
        
        $panier = $session->get("panier", []);
        $dataPanier = [];
        $total = 0;
        foreach($panier as $id => $quantite){
            $img = $photo->find($id);
            $dataPanier[] = [
                "photo"=>$img,
                "quantite"=>$quantite,
          
            ];

            $total += $img->getPrix() * $quantite;
        }
      
        Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        $result = Stripe\Charge::create ([
                "amount" => $total*100,
                "currency" => "eur",
                "source" => $request->request->get('stripeToken'),
                "description" => "Binaryboxtuts Payment Test"
        ]);



        $this->addFlash(
            'success',
            'Payment Successful!'
        );

        $commande = new Commande();
        $commande->setRef($result['id']);
        $commande->setMontant($result['amount']/100);
        $commande->setUrl($result['receipt_url']);
        $commande->setIsPaid($result['paid']);
        // $commande->setUser($this->getUser());
        $entity->persist($commande);
        $entity->flush();
        unset($panier);
        return $this->redirectToRoute('app_stripe', [], Response::HTTP_SEE_OTHER);
    }

}