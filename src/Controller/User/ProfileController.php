<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Manager\Framework\Command\CommandBus;
use App\Manager\Module\User\Command\DeleteAccount\DeleteAccountCommand;
use App\Manager\Module\User\Command\UpdateProfile\UpdateProfileCommand;
use App\Manager\Module\User\Form\UpdateProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, CommandBus $commandBus, TranslatorInterface $translator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $command = new UpdateProfileCommand();
        $command->setEmail($user->getEmailAddress() ?? '');
        $command->setLocale($user->getLocale());

        if ($user->getManager()) {
            $command->setManagerName($user->getManager()->getName() ?? '');
        }

        $form = $this->createForm(UpdateProfileFormType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $commandBus->handle($command);
                $this->addFlash('success', $translator->trans('profile.update_success'));

                return $this->redirectToRoute('app_profile', ['_locale' => $command->getLocale()]);
            } catch (BadRequestHttpException $e) {
                $form->get('currentPassword')->addError(new FormError($translator->trans($e->getMessage())));
            }
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, CommandBus $commandBus, TranslatorInterface $translator): Response
    {
        if (!$this->isCsrfTokenValid('delete-account', $request->request->get('_token'))) {
            $this->addFlash('error', $translator->trans('profile.delete.invalid_csrf'));
            return $this->redirectToRoute('app_profile');
        }

        $commandBus->handle(new DeleteAccountCommand());

        $this->addFlash('success', $translator->trans('profile.delete.success'));

        return $this->redirectToRoute('app_login');
    }
}
