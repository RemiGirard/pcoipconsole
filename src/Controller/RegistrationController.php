<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;

use App\Security\StubAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // create a form for new user, user are without any role by default
        $user = new User();
        //this line is just here to pass the validation value and is never used
        $user->setPassword("password");
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // add the user to the db when the form is submitted

        // the following help to debug the $form->isValid fail : $serializer = $this->get('serializer'); $response = $serializer->serialize($form,'json'); return new Response($response);

        //below it should be with parameter the isValid function but it doesnt work for now
        //this return false :if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles([""]);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/grantrole/{user}", name="app_grantrole")
     */
    public function grantrole($user)
    {
        // manually grant a user a role, only accessible to user with already role ROLE_ADMIN
        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)->findOneByUsername($user);

        $user->setRoles(["ROLE_OPERATOR"]);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response("success grant ROLE_OPERATOR for ".$user->getUsername());
    }
}
