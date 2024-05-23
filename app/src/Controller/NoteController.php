<?php
/**
 * Note controller.
 */

namespace App\Controller;

use App\Entity\Note;
use App\Form\Type\NoteType;
use App\Service\NoteService;
use App\Service\NoteServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
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
    public function __construct(private readonly noteServiceInterface $noteService, private readonly TranslatorInterface $translator)
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
    public function index(#[MapQueryParameter] int $page=1): Response
    {
        $pagination = $this->noteService->getPaginatedList($page);

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
        $note = new note();
        $form = $this->createForm(NoteType::class, $note);
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
    public function edit(Request $request, Note $note): Response
    {
        $form = $this->createForm(
            NoteType::class,
            $note,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('note_edit', ['id' => $note->getId()]),
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


}//end class
