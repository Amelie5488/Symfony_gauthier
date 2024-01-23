<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Photo;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire/{id}-{slug}', name: 'app_commentaire')]
    public function index(EntityManagerInterface $entity, $id, Request $request): Response
    {
        $commentaire = new Commentaire;
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
        $photo = $entity->getRepository(Photo::class)->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire = $form->getData();
            $entity->persist($commentaire);
            $entity->flush();
        }



        return $this->render('commentaire/index.html.twig', [
            'image' => $photo,
            'form'=>$form,
        ]);
    }
}
