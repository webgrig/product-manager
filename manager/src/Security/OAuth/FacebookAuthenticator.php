<?php
namespace App\Security\OAuth;

use App\Security\UserIdentity;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Exception\InvalidStateException;
use KnpU\OAuth2ClientBundle\Exception\MissingAuthorizationCodeException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Security\Exception\IdentityProviderAuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Exception\InvalidStateAuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Exception\NoAuthCodeAuthenticationException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class FacebookAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'oauth.facebook';
    }

    public function authenticate(Request $request): PassportInterface
    {

        $csrf_token = $this->csrfTokenManager->getToken('_csrf_token');
        $request->getSession()->set('state', $csrf_token);
        $client = $this->clientRegistry->getClient('facebook_main');

//        $client->refreshAccessToken($csrf_token);

        $accessToken = $this->fetchAccessToken($client);


        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var FacebookUser $facebookUser */
                $facebookUser = $client->fetchUserFromToken($accessToken);

                $email = $facebookUser->getEmail();

                // 1) have they logged in with Facebook before? Easy!
                $existingUser = $this->entityManager->getRepository(UserIdentity::class)->findOneBy(['facebookId' => $facebookUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2) do we have a matching user by email?
                $user = $this->entityManager->getRepository(UserIdentity::class)->findOneBy(['email' => $email]);

                // 3) Maybe you just want to "register" them by creating
                // a User object
                $user->setFacebookId($facebookUser->getId());
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('oauth.facebook_check');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
