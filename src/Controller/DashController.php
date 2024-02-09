<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Photo;
use App\Form\CategorieType;
use App\Form\PhotoType;
use App\Form\SearchType;
use App\Repository\CommandeRepository;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function PHPUnit\Framework\isNan;
use function PHPUnit\Framework\isNull;

class DashController extends AbstractController
{
    #[Route('/dash', name: 'app_dash')]
    public function index(CommandeRepository $commande, PhotoRepository $respository, Request $request, EntityManagerInterface $entity, PaginatorInterface $paginator): Response
    {
        $commande = $entity->getRepository(Commande::class)->findAll();
        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $search = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        $cat = new Categorie();
        $formCat = $this->createForm(CategorieType::class, $cat);
        $formCat->handleRequest($request);
        $slugger = new AsciiSlugger();

        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->getData();



            $directory = "public/img";
            $file = $form['lien']->getData();
            $file->move($directory, $file->getClientOriginalName());
            $photo->setLien($directory . '/' . $file->getClientOriginalName());
            $photo->setSlug($slugger->slug($form['nom']->getData()));
            $entity->persist($photo);
            $entity->flush();
        }

        if ($formCat->isSubmitted() && $formCat->isValid()) {

            $cat = $formCat->getData();
            $entity->persist($cat);
            $entity->flush();
        }




        // dd($photo);
        $page = $paginator->paginate(
            $respository->paginationQuery(),
            $request->query->get('page', 1),
            5

        );

        //if($search->isSubmitted() && $search->isValid()){
          //  $search = $this->createQueryBuilder('p')
            //-> where('p.name');
            
      //  }


        return $this->render('dash/index.html.twig', [
            'form' => $form,
            'formCat' => $formCat,
            // 'search'=>$search,
            'pagination' => $page,
            'commande'=>$commande,
       
        ]);




    }

    #[route('/dash/delete/{id}', name: 'app_dash_delete')]
    public function delete(EntityManagerInterface $entity, Request $request, $id)
    {
        $photo = $entity->getRepository(Photo::class)->find($id);
        $entity->remove($photo);
        $entity->flush();
        return $this->redirectToRoute("app_dash");
    }

    #[route('/dash/edit/{id}', name: 'app_dash_edit')]
    public function edit(EntityManagerInterface $entity, Request $request, $id, PaginatorInterface $paginator, PhotoRepository $respository)
    {
        $photo = $entity->getRepository(Photo::class)->find($id);
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
   if(empty($form['lien']->getData())){
  
   }else{
            $directory = "public/img";
            $file = $form['lien']->getData();
            $file->move($directory, $file->getClientOriginalName());
            $photo->setLien($directory . '/' . $file->getClientOriginalName());
   }
            $entity->flush();
 
        }

        return $this->render('dash/edit.html.twig', [
            "form" => $form,
            "photo"=>$photo,
   ] );
        }
}
