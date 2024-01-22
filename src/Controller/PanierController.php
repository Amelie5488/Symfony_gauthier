<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, PhotoRepository $photo, Request $request): Response
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

        return $this->render('panier/index.html.twig', compact("dataPanier", "total"));
    }

    #[Route('/panier/{id}', name: 'app_panier_add')]
    public function add(Photo $photo, SessionInterface $session)
    {
        $panier = $session->get("panier",[]);
        $id = $photo->getId();

        if(!empty($panier[$id])){
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $session->set("panier", $panier);

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/{id}', name: 'app_panier_remove')]
    public function remove(Photo $photo, SessionInterface $session)
    {
        $panier = $session->get("panier",[]);
        $id= $photo->getId();

        if(!empty($panier[$id])){
            if($panier > 1){
                $panier[$id]--;
            }else{
                unset($panier[$id]);
            }

            $session->set("panier", $panier);

            return $this->redirectToRoute('app_panier');

        }
    }

    #[Route('/delete/{id}', name: 'app_panier_delete')]
    public function delete(Photo $photo, SessionInterface $session)
    {
        $panier = $session->get("panier", []);
        $id = $photo->getId();

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set("panier", $panier);

        return $this->redirectToRoute('app_panier');
    }

}
