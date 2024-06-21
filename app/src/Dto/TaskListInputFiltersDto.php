<?php
/**
 * Task list input filters DTO.
 */

namespace App\Dto;

/**
 * Class TaskListInputFiltersDto.
 */
class TaskListInputFiltersDto
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
