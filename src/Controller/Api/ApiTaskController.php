<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiTaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/task', name: 'get_tasks', methods: ['GET'])]
    public function getAll(): Response
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();

        if($tasks===null){
            return $this->json(null,Response::HTTP_NOT_FOUND);
        }

        return $this->json($tasks,Response::HTTP_OK,[],['groups'=>'task_r']);
    }

    #[Route('/api/task/{task}', name: 'get_task', methods: ['GET'])]
    public function get($task): Response
    {
        $task = $this->entityManager->getRepository(Category::class)->findOneBy(['id'=>$task]);

        if($task===null){
            return $this->json(null,Response::HTTP_NOT_FOUND);
        }

        return $this->json($task,Response::HTTP_OK,[],['groups'=>'task_r']);
    }

    #[Route('/api/task', name: 'add_task', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if(empty($data['label'])){
            return $this->json(['message'=>'Tous les champs doivent être renseignés'],Response::HTTP_BAD_REQUEST);
        }
        $label = $data['label'];
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['id'=> $data['category']]);;


        $newTask= new Task();
        $newTask->setLabel($label);
        $newTask->setCategory($category);

        $this->entityManager->persist($newTask);
        $this->entityManager->flush();

        return $this->json($newTask,Response::HTTP_CREATED,[],['groups'=>'task_r']);
    }

    #[Route('/api/task/{task}', name: 'put_patch_task', methods: ['PUT','PATCH'])]
    public function putPatch(Request $request, $task): Response
    {
        $data = json_decode($request->getContent(), true);

        $taskObject = $this->entityManager->getRepository(Task::class)->findOneBy(['id'=>$task]);

        if($taskObject===null){
            throw $this->createNotFoundException(sprintf(
                'Pas de task trouvé "%s"',
                $task
            ));
        }

        if($request->getMethod()==='PUT' and (empty($data['label'])  )){
            return $this->json(['message'=>'Tous les champs doivent être renseignés'],Response::HTTP_BAD_REQUEST);
        }

        if(!empty($data['label'])) $taskObject->setLabel($data['label']);



        $this->entityManager->flush();

        return $this->json($taskObject,Response::HTTP_OK);
    }

    #[Route('/api/task/{task}', name: 'delete_task', methods: ['DELETE'])]
    public function delete( $task): Response
    {
        $taskObject = $this->entityManager->getRepository(Task::class)->findOneBy(['id'=>$task]);

        if($taskObject===null){
            throw $this->createNotFoundException(sprintf(
                'Pas de task trouvé "%s"',
                $task
            ));
        }

        $this->entityManager->remove($taskObject);
        $this->entityManager->flush();

        $tasks = $this->entityManager->getRepository(Task::class)->findAll();

        return $this->json($tasks,Response::HTTP_OK,[],['groups'=>'task_r']);
    }
}
