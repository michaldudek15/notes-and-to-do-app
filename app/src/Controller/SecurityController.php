<?php

namespace App\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\Type\RegistrationType;
use App\Service\NoteServiceInterface;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class SecurityController extends AbstractController
{


    /**
     * Constructor.
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher)
    {

    }//end __construct()


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);

    }//end login()


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');

    }//end logout()


    #[Route(
        '/register',
        name: 'register',
        methods: 'GET|POST',
    )]
    public function register(Request $request): Response
    {
        $user = new user();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

            $user->setRoles([UserRole::ROLE_USER->value]);
            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'security/register.html.twig',
            ['form' => $form->createView()]
        );

    }//end register()


    // #[Route(
    // '/changeCredentials',
    // name: 'changeCredentials',
    // methods: 'GET|POST',
    // )]
    // public function changeCredentials(Request $request): Response
    // {
    // $user = $this->getUser();
    // $form = $this->createForm(RegistrationType::class, $user);
    // $form->handleRequest($request);
    //
    // if ($form->isSubmitted() && $form->isValid()) {
    // $user->setEmail($user->getEmail());
    // $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
    //
    // $this->userService->save($user);
    //
    // $this->addFlash(
    // 'success',
    // $this->translator->trans('message.changed_successfully')
    // );
    //
    // return $this->redirectToRoute('note_index');
    // }
    //
    // return $this->render(
    // 'security/register.html.twig',
    // ['form' => $form->createView()]
    // );
    //
    // }//end changeCredentials()
}//end class
