<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    #[Route('/photo/{id}', name: 'app_photo')]
    public function index(EntityManagerInterface $entity, $id): Response
    {
        $photo = $entity ->getRepository(Photo::class)->findBy(["categorie" => $id]);
        return $this->render('photo/index.html.twig', [
            'image' => $photo,
        ]);
    }
}
