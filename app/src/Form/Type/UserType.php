<?php

/**
 * User type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserType.
 */
class UserType extends AbstractType
{
    /**
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly TranslatorInterface $translator)
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
        $builder->add(
            'email',
            EmailType::class,
            [
                'label'    => 'label.email',
                'required' => false,
                'attr'     => [
                    'minlength' => 3,
                    'maxlength' => 180,
                ],            ]
        );

        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'label.new_password',
                    'attr'     => [
                        'minlength' => 8,
                        'maxlength' => 64,
                    ], ],
                'second_options'  => ['label' => 'label.repeat_new_password',
                    'attr'     => [
                        'minlength' => 8,
                        'maxlength' => 64,
                    ], ],
                'invalid_message' => $this->translator->trans('message.invalid_repeated_password'),
                'mapped'          => false,
                'label'           => 'label.password',
                'required'        => true,
            ]
        );
    }// end buildForm()

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
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
        return 'user';
    }// end getBlockPrefix()
}// end class
