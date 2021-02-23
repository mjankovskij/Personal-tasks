<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\User;
use App\Form\AddstatusFormType;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

class StatusController extends AbstractController
{
    /**
     * @Route("/statuses/{sort?}", name="statuses_page")
     */
    public function f_render(string $sort = null): Response
    {

        $globalArray['base_url'] = $this->generateUrl('statuses_page');
        $globalArray['sort'] = [
            'id_status' => [['id' => 'DESC'], ['name' => 'By id DESC']], // highest to lowest
            'id_status_a' => [['id' => 'ASC'], ['name' => 'By id ASC']], // lowest to highest
            'name' => [['name' => 'DESC'], ['name' => 'By name DESC']],
            'name_a' => [['name' => 'ASC'], ['name' => 'By name ASC']],
        ];

        if (array_key_exists($sort, $globalArray['sort'])) {
            $sortArray = $globalArray['sort'][$sort][0];
        } else {
            $sortArray = ['id' => 'DESC'];
        }

        $globalArray['statuses'] = $this->getDoctrine()
            ->getRepository(Status::class)
            ->findBy([], $sortArray);

        $securityContext = $this->container->get('security.authorization_checker');

        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $form = $this->createForm(AddstatusFormType::class);
            $globalArray['addstatusForm'] = $form->createView();
        } else {
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $globalArray['registrationForm'] = $form->createView();
            $formLogin = $this->createForm(LoginFormType::class, $user);
            $globalArray['loginForm'] = $formLogin->createView();
        }

        return $this->render('pages/statuses.html.twig', $globalArray);
    }

    /**
     * @Route("/statuses/save", name="app_savestatus", priority=10, methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function f_save(Request $request): Response
    {
        $id = $request->request->get('addstatus_form')['id'];

        $status = new Status();
        $form = $this->createForm(AddstatusFormType::class, $status);
        $form = $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($id == '') {

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($status);
                $entityManager->flush();
                return new Response('Ok', 200);
            } else {

                $status = $this->getDoctrine()
                    ->getRepository(Status::class)
                    ->findOneBy(['id' => $id]);

                $status
                    ->setName($form->get('name')->getData());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($status);
                $entityManager->flush();
                return new Response('Ok', 200);
            }
        }

        $error = $this->getErrorMessages($form)[0];
        
        return new JsonResponse([
            'message' => $error
        ], 400);
    }

    /**
     * @Route("/statuses/get", name="app_getstatus", priority=10, methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function f_get(Request $request): Response
    {
        $statusArray = [];
        $id = $request->get('id');


        $status = $this->getDoctrine()
            ->getRepository(Status::class)
            ->findOneBy(['id' => $id]);

        $statusArray['id'] = $id;
        $statusArray['name'] = $status->getName();;

        return new JsonResponse([
            'message' => $statusArray
        ], 200);
    }

    /**
     * @Route("/statuses/delete", name="app_deletestatus", priority=10, methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function f_delete(Request $request): Response
    {
        $id = $request->get('id');

        $status = $this->getDoctrine()
            ->getRepository(Status::class)
            ->findOneBy(['id' => $id]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($status);
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
