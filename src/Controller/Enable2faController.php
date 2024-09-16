<?php

namespace App\Controller;

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
    public function setup2FA(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('auth_code');
            if ($this->googleAuthenticator->checkCode($user, $code)) {
                $user->setTwoFactorEnabled(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', '2FA activée avec succès!');
                return $this->redirectToRoute('app_profile');
            } else {
                $this->addFlash('error', 'Le code est incorrect, veuillez réessayer.');
            }
        }

        $qrCodeUrl = $this->generateUrl('app_2fa_qrcode');

        return $this->render('security/setup_2fa.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);

    }

    #[Route('/qrcode', name: 'app_2fa_qrcode')]
    public function showQrCode(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getGoogleAuthenticatorSecret()) {
            throw $this->createNotFoundException('Secret 2FA non trouvé pour l\'utilisateur.');
        }

        $qrCodeContent = $this->googleAuthenticator->getQRContent($user);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }
}
