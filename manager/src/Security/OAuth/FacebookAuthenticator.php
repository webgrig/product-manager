<?php
namespace App\Security\OAuth;

use App\Model\User\UseCase\Network\Auth\Command;
use App\Model\User\UseCase\Network\Auth\Handler;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class FacebookAuthenticator extends OAuth2Authenticator
{
    private UserProviderInterface $userProvider;
    private Handler $handler;
    private ClientRegistry $clientRegistry;

    public function __construct(
        ClientRegistry $clientRegistry,
        RouterInterface $router,
        UserProviderInterface $userProvider,
        Handler $handler
    )
    {
        $this->router = $router;
        $this->userProvider = $userProvider;
        $this->handler = $handler;
        $this->clientRegistry = $clientRegistry;
    }

    public function supports(Request $request): ?bool
    {
        return 'oauth.facebook_check' === $request->attributes->get('_route');
    }


    public function authenticate(Request $request): PassportInterface
    {
        $client = $this->clientRegistry->getClient('facebook_main');

        $accessToken = $this->fetchAccessToken($client);


        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var FacebookUser $facebookUser */
                $facebookUser = $client->fetchUserFromToken($accessToken);

                $network = 'facebook';
                $id = $facebookUser->getId();
                $username = $network . ':' . $id;

                $command = new Command($network, $id);
                $command->firstName = $facebookUser->getFirstName();
                $command->lastName = $facebookUser->getLastName();
                try {
                    return $this->userProvider->loadUserByUsername($username);
                } catch (UserNotFoundException $e) {
                    $this->handler->handle($command);
                    return $this->userProvider->loadUserByUsername($username);
                }
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('home');

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
