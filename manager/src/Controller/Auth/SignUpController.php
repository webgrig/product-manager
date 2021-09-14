<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Model\User\UseCase\SignUp;
use App\ReadModel\User\UserFetcher;
use App\Security\EmailVerifier;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SignUpController extends AbstractController
{
    private $users;
    private $logger;
    private $emailVerifier;

    public function __construct(
        UserFetcher $users,
        LoggerInterface $logger,
        EmailVerifier $emailVerifier
    )
    {
        $this->users = $users;
        $this->logger = $logger;
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/signup", name="auth.signup")
     * @param Request $request
     * @param SignUp\Request\Handler $handler
     * @return Response
     */
    public function request(Request $request, SignUp\Request\Handler $handler): Response
    {
        $command = new SignUp\Request\Command();

        $form = $this->createForm(SignUp\Request\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Check your email.');
                return $this->redirectToRoute('home');
            } catch (\DomainException $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auth/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/signup/{token}", name="auth.signup.confirm")
     * @param Request $request
     * @param string $token
     * @param SignUp\Confirm\ByToken\Handler $handler
     * @param UserProviderInterface $userProvider
     * @return Response
     */
    public function confirm(
        Request $request,
        string $token,
        SignUp\Confirm\ByToken\Handler $handler,
        UserProviderInterface $userProvider
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$user = $this->users->findBySignUpConfirmToken($token)) {
            $this->addFlash('error', 'Incorrect or already confirmed token.');
            return $this->redirectToRoute('auth.signup');
        }

        $command = new SignUp\Confirm\ByToken\Command($token);

        try {
            $handler->handle($command);
            $this->emailVerifier->handleEmailConfirmation($request, $userProvider->loadUserByUsername($user->email));

        } catch (VerifyEmailExceptionInterface $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('home');
    }
}
