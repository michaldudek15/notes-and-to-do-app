<?php
/**
 * Note type.
 */

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\Tag;
use App\Form\DataTransformer\TagsDataTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class NoteType.
 */
class NoteType extends AbstractType
{


    public function __construct(private readonly TagsDataTransformer $tagsDataTransformer, private readonly TranslatorInterface $translator)
    {

    }//end __construct()


    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $whitespaceError = $this->translator->trans('message.whitespace_error');

        $builder->add(
            'title',
            TextType::class,
            [
                'label'    => 'label.title',
                'required' => true,
                'attr'     => ['max_length' => 64],
            ]
        );
        $builder->add(
            'content',
            TextAreaType::class,
            [
                'label'    => 'label.content',
                'required' => true,
                'attr'     => [
                    'max_length' => 65535,
                    'rows'       => 10,
                ],
            ]
        );
        $builder->add(
            'category',
            EntityType::class,
            [
                'class'        => Category::class,
                'choice_label' => function ($category): string {
                    return $category->getTitle();
                },
                'label'        => 'label.category',
                'placeholder'  => 'label.none',
                'required'     => true,
            ]
        );
        $builder->add(
            'tags',
            TextType::class,
            [
                'label'    => 'label.tags',
                'required' => false,
                'attr'     => ['max_length' => 128],
            // 'constraints' => [
            // new Assert\Regex(
            // [
            // 'pattern' => '/^([a-zA-Z0-9]+, )*[a-zA-Z0-9]+$/',
            // 'message' => $whitespaceError,
            // ]
            // ),
            // ],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );

    }//end buildForm()


    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Note::class]);

    }//end configureOptions()


    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'note';

    }//end getBlockPrefix()


}//end class
