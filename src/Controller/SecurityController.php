<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorTrait;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;
    private EntityManagerInterface $entityManager;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $entityManager)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/2fa/setup', name: 'app_2fa_setup')]
    public function setup2FA(Request $request): Response
    {
        $user = $this->getUser();

        // Générer une clé secrète et l'activer pour l'utilisateur s'il ne l'a pas encore
        if (!$user->isGoogleAuthenticatorEnabled()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);

            // Persister les changements dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        // Générer l'URL du QR code pour l'application Google Authenticator
        $qrCodeUrl = $this->googleAuthenticator->getQRContent($user);

        return $this->render('security/setup_2fa.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }
}

