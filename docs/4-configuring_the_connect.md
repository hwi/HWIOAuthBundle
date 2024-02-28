Step 4: Configuring the connect (register) layer
================================================

### A) Create the `Connector` class

We need to create new class that will implement `HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface` interface.
That class will be responsible for persisting `User` entities with given resource owner identifiers. For simple implementation
we can define the class as:
```yaml
services:
    App\Security\OAuthConnector:
        arguments:
            $properties:
                'facebook': 'facebook'
                'google': 'google'
```
And implement it:
```php
namespace App\Security;

final class OAuthConnector implements AccountConnectorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly array $properties
    ) {
    }

    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!isset($this->properties[$response->getResourceOwner()->getName()])) {
            return;
        }

        $property = new PropertyAccessor();
        $property->setValue($user, $this->properties[$response->getResourceOwner()->getName()], $response->getUserIdentifier());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
```

### B) Create form & form handler

In order to properly display & handle form of user registration, we need to create two classes: form & form handler.
The first one will be Symfony Form, let's call it `RegistrationFormType`:
```php
namespace App\Form;

final class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('username', TextType::class, ['required' => false])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the handler
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
```
Now we need to handle the form data, to do that we need new class that implements `HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface` interface:
```php
namespace App\Security;

final readonly class FormHandler implements RegistrationFormHandlerInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation): bool
    {
        $user = new User();
        $user->setEmail($userInformation->getEmail());
        $user->setUsername($userInformation->getNickname());
        $user->setFirstName($userInformation->getFirstName());
        $user->setLastName($userInformation->getLastName());

        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            return true;
        }

        return false;
    }
}
```

### C) Configure connect functionality

As final step, we need to enable connect functionality both in the bundle & on the security firewall:
```yaml
# config/packages/hwi_oauth.yaml
hwi_oauth:
    connect:
        account_connector: App\Security\OAuthConnector
        registration_form: App\Form\RegistrationFormType
        registration_form_handler: App\Security\FormHandler
```
In firewall configuration we need to change `failure_path` to the bundle route named `hwi_oauth_connect_registration`:
```yaml
# config/packages/security.yaml
security:
    enable_authenticator_manager: true

    firewalls:
        main:
            pattern: ^/
            oauth:
                resource_owners:
                    facebook: "/login/check-facebook"
                    google:   "/login/check-google"
                login_path:   /login
                failure_path: hwi_oauth_connect_registration

                oauth_user_provider:
                    service: my.oauth_aware.user_provider.service
```

## That was it!

Now when user tries to use login functionality without having account in your application, bundle will redirect
on new page where user can finish creating account.

Given above examples are not production ready, and you need to adjust them to your needs.

Remember that you can (and you should) also [overwrite templates provided by this bundle](https://symfony.com/doc/current/bundles/override.html#templates).

## Bonus: connect existing accounts

Additional functionality is allowing users to connect their existing accounts with resource owners.

```jinja
{% for owner in hwi_oauth_resource_owners() %}
    {% if attribute(app.user, owner) is empty %}
        <a href="{{ path('hwi_oauth_connect_service', {'service': owner}) }}">{{ owner | trans({}, 'HWIOAuthBundle') }}</a> <br />
    {% else %}
        <span>{{ owner | trans({}, 'HWIOAuthBundle') }} connected</span> <br />
    {% endif %}
{% endfor %}
```

[Return to the index.](index.md)
