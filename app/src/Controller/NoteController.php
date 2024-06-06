<?php
/**
 * Note controller.
 */

namespace App\Controller;

use App\Dto\NoteListInputFiltersDto;
use App\Entity\Category;
use App\Entity\Note;
use App\Entity\User;
use App\Form\Type\NoteType;
use App\Resolver\NoteListInputFiltersDtoResolver;
use App\Service\CategoryServiceInterface;
use App\Service\NoteService;
use App\Service\NoteServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NoteController.
 */
#[Route('/note')]
class NoteController extends AbstractController
{


    /**
     * Constructor.
     */
    public function __construct(private readonly noteServiceInterface $noteService, private readonly TranslatorInterface $translator, private readonly CategoryServiceInterface $categoryService)
    {

    }//end __construct()


    /**
     * Index action.
     *
     * @param integer $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(name: 'note_index', methods: 'GET')]
    public function index(#[MapQueryString(resolver: NoteListInputFiltersDtoResolver::class)] NoteListInputFiltersDto $filters, #[MapQueryParameter] int $page=1): Response
    {
        $user       = $this->getUser();
        $pagination = $this->noteService->getPaginatedList(
            $page,
            $user,
            $filters
        );

        return $this->render('note/index.html.twig', ['pagination' => $pagination]);

    }//end index()


    /**
     * Show action.
     *
     * @param Note $note Note
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'note_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted('VIEW', subject: 'note')]
    public function show(Note $note): Response
    {
        return $this->render('note/show.html.twig', ['note' => $note]);

    }//end show()


    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'note_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        $note = new note();
        $note->setAuthor($user);
        $form = $this->createForm(NoteType::class, $note, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->noteService->save($note);
            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('note_index');
        }

        return $this->render(
            'note/create.html.twig',
            ['form' => $form->createView()]
        );

    }//end create()


    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Note    $note    Note entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'note_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    #[IsGranted('VIEW', subject: 'note')]
    public function edit(Request $request, Note $note): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(
            NoteType::class,
            $note,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('note_edit', ['id' => $note->getId()]),
                'user'   => $user,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->noteService->save($note);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('note_index');
        }

        return $this->render(
            'note/edit.html.twig',
            [
                'form' => $form->createView(),
                'note' => $note,
            ]
        );

    }//end edit()


    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Note    $note    Note entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'note_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    #[IsGranted('VIEW', subject: 'note')]
    public function delete(Request $request, Note $note): Response
    {
        $form = $this->createForm(
            FormType::class,
            $note,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('note_delete', ['id' => $note->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->noteService->delete($note);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('note_index');
        }

        return $this->render(
            'note/delete.html.twig',
            [
                'form' => $form->createView(),
                'note' => $note,
            ]
        );

    }//end delete()


}//end class
