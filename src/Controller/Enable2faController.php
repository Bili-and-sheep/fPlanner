<?php

namespace App\Controller;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Doctrine\ORM\EntityManagerInterface; // Correct import for Doctrine
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

class Enable2faController extends AbstractController
{
    private GoogleAuthenticatorInterface $googleAuthenticator;
    private EntityManagerInterface $entityManager;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $entityManager)
    {
        $this->googleAuthenticator = $googleAuthenticator;
        $this->entityManager = $entityManager;
    }

    #[Route('/enable2fa', name: 'app_enable2fa')]
    public function setup2FA(Request $request, GoogleAuthenticatorInterface $googleAuthenticator, BuilderInterface $customQrCodeBuilder): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('auth_code');
            if ($googleAuthenticator->checkCode($user, $code)) {
                $user->setTwoFactorEnabled(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', '2FA activée avec succès!');
                return $this->redirectToRoute('app_profile');
            } else {
                $this->addFlash('error', 'Le code est incorrect, veuillez réessayer.');
            }
        }

        $qrCodeUrl = $googleAuthenticator->getQRContent($user);

        return $this->render('security/setup_2fa.html.twig', [

            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }
}
