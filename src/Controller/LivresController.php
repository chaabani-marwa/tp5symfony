<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Form\LivresType;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\throwException;

final class LivresController extends AbstractController
{
    #[Route('/admin/livres/delete/{id}', name: 'app_livres_delete')]
    public function delete(Livres $livre,EntityManagerInterface $em): Response
    {
        $em->remove($livre);
        $em->flush();
        //dd($livre);
                    return $this->redirectToRoute('admin_livres');

    }
    #[Route('/admin/livres/update/{id}', name: 'app_livres_update', methods: ['GET', 'POST'])]
    public function update(Request $request, Livres $livre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LivresType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le livre a été mis à jour.');

            return $this->redirectToRoute('admin_livres');
        }

        return $this->render('livres/edit.html.twig', [
            'f' => $form,
            'livre' => $livre,
        ]);
    }
    #[Route('/admin/livres', name: 'admin_livres')]
    public function all(LivresRepository $rep,PaginatorInterface $paginator, Request $request): Response
    {
        $livres = $paginator->paginate(
            $rep->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        //dd($livres);
        return $this->render('livres/all.html.twig', ['livres'=>$livres]);
    }
    #[Route('admin/livres/show/{id}', name: 'app_livres_show')]
    //paramConverter
    public function show(Livres $livre): Response
    {
        if(!$livre)
        {throw $this->createNotFoundException('No book found  ');}

        return $this->render('livres/show.html.twig', ['livre'=>$livre]);
    }
    #[Route('/admin/livres/show2', name: 'app_livres_show2')]
    public function show2(LivresRepository $rep): Response
    { $livre=$rep->findOneBy(['titre'=>'titre 1','editeur'=>'Eni']);

        dd($livre);
    }
    #[Route('admin/livres/show3', name: 'app_livres_show3')]
    public function show3(LivresRepository $rep): Response
    { $livres=$rep->findBy(['titre'=>'titre 1','editeur'=>'Eyrolles'],['prix'=>'DESC']);

        dd($livres);
    }



    #[Route('/admin/livres/create', name: 'admin_livres_create')]
    public function create(Request $request,EntityManagerInterface $em): Response
    {   $livre=new Livres();
        //afficher le formulaire
        $form=$this->createForm(LivresType::class,$livre);
        //traitement des données issues
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($livre);
            $em->flush();
            $this->addFlash('success','Le livre a été bien ajouté');
            return $this->redirectToRoute('admin_livres');


        }

        return $this->render('livres/create.html.twig', [
            'f' => $form,
        ]);
    }
}
