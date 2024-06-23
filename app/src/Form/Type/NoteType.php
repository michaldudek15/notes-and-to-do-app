<?php

/**
 * Note type.
 */

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\User;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NoteType.
 */
class NoteType extends AbstractType
{
    /**
     * @param CategoryService     $categoryService     Category service
     * @param TagsDataTransformer $tagsDataTransformer Tags data transofrmer
     * @param TranslatorInterface $translator          Translator
     */
    public function __construct(private readonly CategoryService $categoryService, private readonly TagsDataTransformer $tagsDataTransformer, private readonly TranslatorInterface $translator)
    {
    }// end __construct()

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

        $user = $options['user'];

        dump($this->categoryService->getCategoriesByUser($user));

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
            TextareaType::class,
            [
                'label'    => 'label.content',
                'required' => true,
                'attr'     => [
                    'max_length' => 65535,
                    'rows'       => 10,
                ],
            ]
        );

        // $categoriesByAuthor = $this->categoryService->$this->categoryRepository->getCategoriesByAuthor($user);
        // foreach ($categoriesByAuthor as $category) {
        // $choicesArray[] = $category->getTitle();
        // }
        $builder->add(
            'category',
            EntityType::class,
            [
                'class'        => Category::class,
                'choice_label' => function ($category): string {
                    return $category->getTitle();
                },
                'label'        => 'label.category',
                'required'     => true,
                'choices'      => $this->categoryService->getCategoriesByUser($user),
            ]
        );

        $builder->add(
            'tags',
            TextType::class,
            [
                'label'    => 'label.tags',
                'required' => false,
                'attr'     => ['max_length' => 128],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );

        $builder->get('tags')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $tagsFormField  = $event->getForm();
                $tagsFieldValue = $event->getData();
                if (!empty($tagsFieldValue)) {
                    if (!preg_match_all('/^([a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+, *)*[a-zA-Z0-9ąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/', $tagsFieldValue)) {
                        $tagsFormField->addError(new FormError('label.invalid_tags'));
                    }
                }
            }
        );
    }// end buildForm()

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Note::class]);
        $resolver->setDefaults(['user' => 0]);
    }// end configureOptions()

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
    }// end getBlockPrefix()
}// end class
