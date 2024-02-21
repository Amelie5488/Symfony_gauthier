<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Commentaire;
use App\Entity\Photo;
use App\Form\CategorieType;
use App\Form\PhotoType;
use App\Form\SearchType;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\CommentaireRepository;
use App\Repository\PhotoRepository;
use App\Service\AlertServiceInterface;

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

    public function __construct(
    readonly private AlertServiceInterface $alertService,
    readonly private CommandeRepository $commande,
    readonly private PhotoRepository $respository,
    readonly private EntityManagerInterface $entity,
    readonly private CommentaireRepository $commentaireRepository,
    readonly private CategorieRepository $categorieRepository,
    )
    {
        
    }
    #[Route('/dash', name: 'app_dash')]
    public function index( Request $request, PaginatorInterface $paginator): Response
    {
       $commande = $this->entity->getRepository(Commande::class)->findAll();
   
       $commentaire = $this->commentaireRepository->findAll();
       $gallerie= $this->categorieRepository->findAll();

        $text="";
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



            $directory = "img";
            
            $file = $form['lien']->getData();
            $file->move($directory, $file->getClientOriginalName());
           
            $photo->setLien($file->getClientOriginalName());
            $photo->setSlug($slugger->slug($form['nom']->getData()));
           
            $this->entity->persist($photo);
            $this->entity->flush();
            
            $text= "Photo bien ajoutée";
            
        }

        if ($formCat->isSubmitted() && $formCat->isValid()) {

          
            $directory = "img";
            
            $file = $formCat['image']->getData();
            $file->move($directory, $file->getClientOriginalName());
            
            $cat->setImage($file->getClientOriginalName());
            $cat->setSlug($slugger->slug($formCat['nom']->getData()));

            $this->entity->persist($cat);
            $this->entity->flush();

          

            $text= "Catégorie bien ajoutée";
        
        }


        if ($form->isSubmitted() && $form->isValid()|| $formCat->isSubmitted() && $formCat->isValid() ){
            $this->alertService->success($text);
            return $this->redirectToRoute("app_dash");
        }



        // dd($photo);
        $page = $paginator->paginate(
            $this->respository->paginationQuery(),
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
            'commentaire'=>$commentaire,
            'categorie'=>$gallerie,
       
        ]);




    }

    #[route('/dash/delete/{id}', name: 'app_dash_delete')]
    public function delete(EntityManagerInterface $entity, Request $request, $id)
    {
        $photo = $entity->getRepository(Photo::class)->find($id);       
        $entity->remove($photo);
        $entity->flush();
        $this->alertService->success("Photo supprimée");
        return $this->redirectToRoute("app_dash");
    }

    #[route('/dash/deleteCat{id}', name : 'app_dash_deleteCat')]
    public function deleteCat(EntityManagerInterface $entity, $id)
    {
        $cat = $entity->getRepository(Categorie::class)->find($id);
        $entity->remove($cat);
        $entity->flush();
        $this->alertService->success("Catégorie suprrimée");
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
            $photo->setLien($file->getClientOriginalName());
   }
            $entity->flush();
 
        }
        $this->alertService->success("Photo modifiée");
        return $this->render('dash/edit.html.twig', [
            "form" => $form,
            "photo"=>$photo,
   ] );
        }

    #[route('/dash/deleteComm{id}', name:'app_dash_deleteCom')]
    public function deleteCom(EntityManagerInterface $entity, $id)
    {
        $commentaire = $entity->getRepository(Commentaire::class)->find($id);
        $entity->remove($commentaire);
        $entity->flush();
        $this->alertService->success("Commentaire supprimé");
        
        return $this->redirectToRoute("app_dash");
    }
}
