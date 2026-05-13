<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Form\LivresType;
use App\Repository\LivresRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\throwException;

final class LivresController extends AbstractController
{
    #[Route('/catalogue', name: 'app_livres_catalogue')]
    public function catalogue(LivresRepository $rep, PaginatorInterface $paginator, Request $request): Response
    {
        $livres = $paginator->paginate(
            $rep->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('livres/catalogue.html.twig', ['livres' => $livres]);
    }

    #[Route('/livre/{id}', name: 'app_livres_detail')]
    public function detail(Livres $livre): Response
    {
        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        return $this->render('livres/detail.html.twig', ['livre' => $livre]);
    }

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

    #[Route('/panier/ajouter/{id}', name: 'app_cart_add')]
    public function addToCart(Livres $livre, CartService $cartService): Response
    {
        $cartService->addToCart($livre);
        $this->addFlash('success', $livre->getTitre() . ' a été ajouté au panier.');

        return $this->redirectToRoute('app_livres_detail', ['id' => $livre->getId()]);
    }

    #[Route('/panier', name: 'app_cart_view')]
    public function viewCart(CartService $cartService): Response
    {
        $cart = $cartService->getCart();
        $total = $cartService->getCartTotal();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/panier/valider', name: 'app_cart_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request, CartService $cartService): Response
    {
        $cart = $cartService->getCart();
        $total = $cartService->getCartTotal();
        $error = null;
        $success = false;
        $paymentCode = $request->request->get('payment_code', '');

        if ($request->isMethod('POST')) {
            if (empty($cart)) {
                $this->addFlash('warning', 'Votre panier est vide, impossible de valider la commande.');

                return $this->redirectToRoute('app_livres_catalogue');
            }

            if ($paymentCode !== 'SIMULATE123') {
                $error = 'Code de paiement invalide. Utilisez le code de test SIMULATE123.';
            } else {
                $cartService->clearCart();
                $success = true;
                $this->addFlash('success', 'Commande validée avec succès. Paiement simulé accepté.');
            }
        }

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'error' => $error,
            'success' => $success,
            'paymentCode' => $paymentCode,
        ]);
    }

    #[Route('/panier/retirer/{id}', name: 'app_cart_remove')]
    public function removeFromCart(int $id, CartService $cartService): Response
    {
        $cartService->removeFromCart($id);
        $this->addFlash('info', 'Le livre a été retiré du panier.');

        return $this->redirectToRoute('app_cart_view');
    }

    #[Route('/panier/vider', name: 'app_cart_clear')]
    public function clearCart(CartService $cartService): Response
    {
        $cartService->clearCart();
        $this->addFlash('info', 'Le panier a été vidé.');

        return $this->redirectToRoute('app_livres_catalogue');
    }
}
