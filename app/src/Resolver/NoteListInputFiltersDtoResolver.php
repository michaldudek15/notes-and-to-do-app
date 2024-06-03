<?php
/**
 * NoteListInputFiltersDto resolver.
 */

namespace App\Resolver;

use App\Dto\NoteListInputFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * NoteListInputFiltersDtoResolver class.
 */
class NoteListInputFiltersDtoResolver implements ValueResolverInterface
{


    /**
     * Returns the possible value(s).
     *
     * @param Request          $request  HTTP Request
     * @param ArgumentMetadata $argument Argument metadata
     *
     * @return iterable Iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, NoteListInputFiltersDto::class, true)) {
            return [];
        }

        $categoryId = $request->query->get('categoryId');
        $tagId      = $request->query->get('tagId');

        return [new NoteListInputFiltersDto($categoryId, $tagId)];

    }//end resolve()


}//end class
