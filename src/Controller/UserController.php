<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CourrierRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository,CourrierRepository $CourrierRepository): Response
    {
        //@ToDo get user connectd
        $user = $this->getUser();

        return $this->render('user/index.html.twig', [

            'users' => $userRepository->findAll(),
            'user'=> $user,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),

        ]);
    }
    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder,CourrierRepository $CourrierRepository): Response
    {
        $user = $this->getUser();
        $userNew = new User();
        $form = $this->createForm(UserType::class, $userNew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userNew->setPassword(
                $passwordEncoder->encodePassword(
                    $userNew,
                    $form->get('Password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userNew);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }
        return $this->render('user/new.html.twig', [

            'user' => $user,
            'form' => $form->createView(),
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),

        ]);
    }
    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user, CourrierRepository $CourrierRepository): Response
    {
        $user_co = $this->getUser();

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'user_co' => $user_co,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
        ]);
    }
    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder,CourrierRepository $CourrierRepository): Response
    {
        $user_co = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if( $form->get('Password')->getData() != "")
            {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('Password')->getData()
                    ));
            }
            else
            {
                $user->setPassword($user->getPassword());
            }
            /* $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('Password')->getData()
                )
            );*/
           // $user->setPassword($form->get('Password')->getData());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user_co' => $user_co,
            'user' => $user,
            'form' => $form->createView(),
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('user_index');
    }
}
