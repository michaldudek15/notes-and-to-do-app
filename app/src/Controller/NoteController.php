<?php
/**
 * Note controller.
 */

namespace App\Controller;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class NoteController.
 */
#[Route('/note')]
class NoteController extends AbstractController
{
    /**
     * Index action.
     *
     * @param NoteRepository     $noteRepository Note repository
     * @param PaginatorInterface $paginator      Paginator
     *
     * @return Response HTTP response
     */
    #[Route(name: 'note_index', methods: 'GET')]
    public function index(NoteRepository $noteRepository, PaginatorInterface $paginator, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $paginator->paginate(
            $noteRepository->queryAll(),
            $page,
            NoteRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render('note/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Note $note Note entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'note_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Note $note): Response
    {
        return $this->render(
            'note/show.html.twig',
            ['note' => $note]
        );
    }
}
