<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire/{id}-{slug}', name: 'app_commentaire')]
    public function index(EntityManagerInterface $entity, $id): Response
    {
        $photo = $entity->getRepository(Photo::class)->find($id);
        return $this->render('commentaire/index.html.twig', [
            'image' => $photo,
        ]);
    }
}
