<?php
/**
 * Note list input filters DTO.
 */

namespace App\Dto;

/**
 * Class NoteListInputFiltersDto.
 */
class NoteListInputFiltersDto
{


    /**
     * Constructor.
     *
     * @param integer|null $categoryId Category identifier
     * @param integer|null $tagId      Tag identifier
     */
    public function __construct(public readonly ?int $categoryId=null, public readonly ?int $tagId=null)
    {

    }//end __construct()


}//end class
