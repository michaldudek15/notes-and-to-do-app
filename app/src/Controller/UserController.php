<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RoleType;
use App\Form\Type\UserType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface        $userService    User service
     * @param TranslatorInterface         $translator     Translator
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }// end __construct()

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'user_index', methods: 'GET')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        if (null !== ($response = $this->denyUnlessAdmin())) {
            return $response;
        }

        $pagination = $this->userService->getPaginatedList($page, $this->getUser());

        return $this->render(
            'user/index.html.twig',
            [
                'pagination' => $pagination,
                'currentUserId' => $this->getUser()->getId(),
            ]
        );
    }// end index()

    /**
     * Show action.
     *
     * @param User $user User
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'user_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(User $user): Response
    {
        if (null !== ($response = $this->denyUnlessAdmin())) {
            return $response;
        }

        return $this->render('user/show.html.twig', ['user' => $user]);
    }// end show()

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'user_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, User $user): Response
    {
        if (null !== ($response = $this->denyUnlessAdmin())) {
            return $response;
        }

        $form = $this->createForm(
            UserType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();

            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.changed_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end edit()

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'user_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, User $user): Response
    {
        if (null !== ($response = $this->denyUnlessAdmin())) {
            return $response;
        }

        $form = $this->createForm(
            FormType::class,
            $user,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('user_delete', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->delete($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/delete.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end delete()

    /**
     * Change role action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/changeRole', name: 'user_change_role', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function changeRole(Request $request, User $user): Response
    {
        if (null !== ($response = $this->denyUnlessAdmin())) {
            return $response;
        }

        $currentUser = $this->getUser();

        if ($currentUser->getId() === $user->getId()) {
            $this->addFlash('warning', $this->translator->trans('message.cannot_change_own_role'));

            return $this->redirectToRoute('user_index');
        }

        $form = $this->createForm(
            RoleType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('user_change_role', ['id' => $user->getId()]),
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);
            $this->addFlash(
                'success',
                $this->translator->trans('message.changed_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/changeRole.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end changeRole()

    /**
     * Ensure current user has ROLE_ADMIN.
     *
     * @return Response|null Redirect response when access should be denied
     */
    private function denyUnlessAdmin(): ?Response
    {
        if (null === $this->getUser()) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.not_allowed')
            );

            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.not_allowed')
            );

            return $this->redirectToRoute('note_index');
        }

        return null;
    }// end denyUnlessAdmin()
}// end class
