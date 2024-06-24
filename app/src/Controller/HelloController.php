<?php

/**
 * Hello controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class HelloController.
 */
class HelloController extends AbstractController
{
    /**
     * Index action.
     *
     * @return Response HTTP response
     */
    #[Route('/')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    /**
     * Hello action.
     *
     * @param string $name Name
     *
     * @return Response HTTP response
     */
    #[Route(
        'hello/{name}',
        name: 'hello_index',
        requirements: ['name' => '[a-zA-Z]+'],
        defaults: ['name' => 'World'],
        methods: 'GET'
    )]
    public function hello(string $name): Response
    {
        return $this->render(
            'hello/index.html.twig',
            ['name' => $name]
        );
    }
}
