<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use App\Util\Censurator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishController extends AbstractController
{
    /**
     * @Route("/wishes/create", name="wish_create")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Censurator $censurator
    ): Response
    {
        //notre entité vide
        $wish = new Wish();

        //pour préremplir le pseudo dans le formulaire...
        $currentUserUsername = $this->getUser()->getUsername();
        $wish->setAuthor($currentUserUsername);

        //notre formulaire, associée à l'entité vide
        $wishForm = $this->createForm(WishType::class, $wish);

        //récupère les données du form et les injecte dans notre $wish
        $wishForm->handleRequest($request);

        //si le formulaire est soumis et valide...
        if ($wishForm->isSubmitted() && $wishForm->isValid()){
            //hydrate les propriétés absentes du formulaires
            $wish->setIsPublished(true);
            $wish->setDateCreated(new \DateTime());

            //censure les méchants mots
            $purifiedDescription = $censurator->purify($wish->getDescription());
            $wish->setDescription($purifiedDescription);

            //sauvegarde en bdd
            $entityManager->persist($wish);
            $entityManager->flush();

            //affiche un message sur la prochaine page
            $this->addFlash('success', 'Idea successfully added!');

            //redirige vers la page de détails de l'idée fraîchement créée
            return $this->redirectToRoute('wish_detail', ['id' => $wish->getId()]);
        }

        //affiche le formulaire
        return $this->render('wish/create.html.twig', [
            'wishForm' => $wishForm->createView()
        ]);
    }

    /**
     * @Route("/wishes", name="wish_list")
     */
    public function list(WishRepository $wishRepository): Response
    {
        //récupère les Wish publiés, du plus récent au plus ancien
        //on appelle une méthode personnalisée ici pour éviter d'avoir trop de requêtes.
        //Voir le WishRepository.php
        $wishes = $wishRepository->findPublishedWishesWithCategories();

        return $this->render('wish/list.html.twig', [
            //les passe à Twig
            "wishes" => $wishes
        ]);
    }

    /**
     * @Route("/wishes/detail/{id}", name="wish_detail")
     */
    public function detail(int $id, WishRepository $wishRepository): Response
    {
        //récupère ce wish en fonction de l'id présent dans l'URL
        $wish = $wishRepository->find($id);

        //s'il n'existe pas en bdd, on déclenche une erreur 404
        if (!$wish){
            throw $this->createNotFoundException('This wish do not exists! Sorry!');
        }

        return $this->render('wish/detail.html.twig', [
            "wish" => $wish
        ]);
    }
}
