<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\PhotoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashController extends AbstractController
{
    #[Route('/dash', name: 'app_dash')]
    public function index(Request $request, EntityManagerInterface $entity): Response
    {
        $photo= new Photo();
      
        $form=$this->createForm(PhotoType::class, $photo);

        $form -> handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){

            $photo = $form->getData();
      
            

            $directory = "../img";
            $file = $form['lien']->getData();
            $file->move($directory, $file->getClientOriginalName());
            $photo->setLien($directory.'/'.$file->getClientOriginalName());

            $entity->persist($photo);
            $entity->flush();  

            }    
        return $this->render('dash/index.html.twig', [
            'form' => $form,
        ]);
    }}

