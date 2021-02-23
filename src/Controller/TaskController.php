<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Task;
use App\Entity\User;
use App\Form\AddtaskFormType;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/{sort?}/{filter?}", name="tasks_page")
     */
    public function f_render(string $sort = null, string $filter = null): Response
    {

        $globalArray['base_url'] = $this->generateUrl('tasks_page');
        $globalArray['sort'] = [
            'id_task' => [['id' => 'DESC'], ['name' => 'By id DESC']], // highest to lowest
            'id_task_a' => [['id' => 'ASC'], ['name' => 'By id ASC']], // lowest to highest
            'name' => [['task_name' => 'DESC'], ['name' => 'By task name DESC']],
            'name_a' => [['task_name' => 'ASC'], ['name' => 'By task name ASC']]
        ];

        if ($filter) {
            if (!empty($this->getDoctrine()
                ->getRepository(Task::class)
                ->findBy(['status' => $filter]))) {
                $arrayFilters['filter'] = ['status' => $filter];
            }
        }

        $globalArray['tasks_objects'] = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findAll();

        $globalArray['statuses'] = $this->getDoctrine()
            ->getRepository(Status::class)
            ->findAll();

        $globalArray['tasks'] = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findBy($arrayFilters['filter'] ?? [],  $globalArray['sort'][$sort][0] ?? ['id' => 'DESC']);

        $securityContext = $this->container->get('security.authorization_checker');

        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $form = $this->createForm(AddtaskFormType::class);
            $globalArray['addtaskForm'] = $form->createView();
        } else {
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $globalArray['registrationForm'] = $form->createView();
            $formLogin = $this->createForm(LoginFormType::class, $user);
            $globalArray['loginForm'] = $formLogin->createView();
        }

        return $this->render('pages/tasks.html.twig', $globalArray);
    }

    /**
     * @Route("/tasks/save", name="app_savetask", priority=10, methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function f_save(Request $request): Response
    {
        $id = $request->request->get('addtask_form')['id'];

        $task = new Task();
        $form = $this->createForm(AddtaskFormType::class, $task);
        $form = $form->handleRequest($request);
        $date = new DateTime(date("Y/m/d H:i:s"));
        $task->setAddDate($date);
        $completedDate = $request->request->get('addtask_form')['completed_date'];
        if ($completedDate) $task->setCompletedDate(new DateTime($completedDate));


        if ($form->isSubmitted() && $form->isValid()) {
            if ($id == '') {

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($task);
                $entityManager->flush();

                if ($form->get('img')->getData()) {
                    $uploadedFile = $form->get('img')->getData();
                    $destination = $this->getParameter('kernel.project_dir') . '/public/assets/img/tasks/';
                    $uploadedFile->move($destination, $task->getId() . '.jpg');
                    sleep(0.2);
                }
                return new Response('Ok', 200);
            } else {

                $task = $this->getDoctrine()
                    ->getRepository(Task::class)
                    ->findOneBy(['id' => $id]);

                $task
                    ->setTaskName($form->get('task_name')->getData())
                    ->setStatus($form->get('status')->getData())
                    ->setTaskDescription($form->get('task_description')->getData());

                    if ($completedDate) $task->setCompletedDate(new DateTime(date($completedDate)));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($task);
                $entityManager->flush();

                if ($form->get('img')->getData()) {
                    $uploadedFile = $form->get('img')->getData();
                    $destination = $this->getParameter('kernel.project_dir') . '/public/assets/img/tasks/';
                    $uploadedFile->move($destination, $task->getId() . '.jpg');
                    sleep(0.2);
                }
                return new Response('Ok', 200);
            }
        }

        $error = $this->getErrorMessages($form)[0];
        return new JsonResponse([
            'message' => $error
        ], 400);
    }

    /**
     * @Route("/tasks/get", name="app_gettask", priority=10, methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function f_get(Request $request): Response
    {
        $taskArray = [];
        $id = $request->get('id');

        $task = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findOneBy(['id' => $id]);

        $taskArray['id'] = $id;
        $taskArray['task_name'] = $task->getTaskName();
        $taskArray['status'] = $task->getStatus()->getId();
        $taskArray['completed_date'] = $task->getCompletedDate();
        $taskArray['task_description'] = $task->getTaskDescription();

        $taskArray['img'] = $this->container->get('router')->getContext()->getBaseUrl() . $task->getImg();

        return new JsonResponse([
            'message' => $taskArray
        ], 200);
    }

    /**
     * @Route("/tasks/delete", name="app_deletetask", priority=10, methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function f_delete(Request $request): Response
    {
        $id = $request->get('id');

        $task = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findOneBy(['id' => $id]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        return new Response('Ok', 200);
    }

    private function getErrorMessages(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
