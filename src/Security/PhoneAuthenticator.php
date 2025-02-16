<?php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Psr\Log\LoggerInterface;
class PhoneAuthenticator extends AbstractAuthenticator
{
    private $entityManager;
    private $urlGenerator;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_login'
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        
        $phone = $request->request->get('phone', '');
        $password = $request->request->get('password', '');

        if (empty($phone) || empty($password)) {
            throw new CustomUserMessageAuthenticationException('Telefone e senha são obrigatórios.');
        }

        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

        $this->logger->info('verificando usuario '.$normalizedPhone);
        return new Passport(
            new UserBadge($normalizedPhone, fn($userIdentifier) => $this->getUserByPhone($userIdentifier)),
            new PasswordCredentials($password)
        );
    }

    private function getUserByPhone(string $phone): User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);
        $this->logger->info('getUserByPhone ---> '. $phone);
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Usuário não encontrado.');
        }

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}