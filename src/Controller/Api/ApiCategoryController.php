<?php

namespace App\Controller\Api;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiCategoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/api/category', name: 'get_categories', methods: ['GET'])]
    public function getAll(): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        if($categories===null){
            return $this->json(null,Response::HTTP_NOT_FOUND);
        }

        return $this->json($categories,Response::HTTP_OK,[],['groups'=>'category_r']);
    }

    #[Route('/api/category/{category}', name: 'get_category', methods: ['GET'])]
    public function get($category): Response
    {
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['id'=>$category]);

        if($category===null){
            return $this->json(null,Response::HTTP_NOT_FOUND);
        }

        return $this->json($category,Response::HTTP_OK,[],['groups'=>'category_r']);
    }

    #[Route('/api/category', name: 'add_category', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if(empty($data['label']) || empty($data['color'])){
            return $this->json(['message'=>'Tous les champs doivent être renseignés'],Response::HTTP_BAD_REQUEST);
        }
        $label = $data['label'];
        $color = $data['color'];

        $newCategory= new Category();
        $newCategory->setLabel($label)->setColor($color);

        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();

        return $this->json($newCategory,Response::HTTP_CREATED,[],['groups'=>'category_r']);
    }

    #[Route('/api/category/{category}', name: 'put_patch_category', methods: ['PUT','PATCH'])]
    public function putPatch(Request $request, $category): Response
    {
        $data = json_decode($request->getContent(), true);

        $categoryObject = $this->entityManager->getRepository(Category::class)->findOneBy(['id'=>$category]);

        if($categoryObject===null){
            throw $this->createNotFoundException(sprintf(
                'Pas de category trouvé "%s"',
                $category
            ));
        }

        if($request->getMethod()==='PUT' and (empty($data['label']) || empty($data['color']) )){
            return $this->json(['message'=>'Tous les champs doivent être renseignés'],Response::HTTP_BAD_REQUEST);
        }

        if(!empty($data['label'])) $categoryObject->setLabel($data['label']);
        if(!empty($data['color'])) $categoryObject->setColor($data['color']);


        $this->entityManager->flush();

        return $this->json($categoryObject,Response::HTTP_OK);
    }

    #[Route('/api/category/{category}', name: 'delete_category', methods: ['DELETE'])]
    public function delete( $category): Response
    {
        $categoryObject = $this->entityManager->getRepository(Category::class)->findOneBy(['id'=>$category]);

        if($categoryObject===null){
            throw $this->createNotFoundException(sprintf(
                'Pas de category trouvé "%s"',
                $category
            ));
        }

        $this->entityManager->remove($categoryObject);
        $this->entityManager->flush();

        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        return $this->json($categories,Response::HTTP_OK,[],['groups'=>'category_r']);
    }
}

