<?php

namespace App\Controller\Website\Frontend;

use App\Form\Website\Contact\ContactForm;
use App\Service\Core\MailService;
use App\Service\Website\CMSUtils;
use App\Service\Website\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ContactPageController extends CmsController implements CmsControllerImpl
{
    public function __construct(
        MenuService $menuService,
        EntityManagerInterface $em,
        CMSUtils $CMSUtils,
        PaginatorInterface $paginator,
        private readonly MailService $mailService,
    ) {
        parent::__construct($menuService, $em, $CMSUtils, $paginator);
    }

    public function index(Request $request, string $menuId, ?string $token = null): Response
    {
        $form = $this->createForm(ContactForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $email = (new Email())
                    ->from(new Address('noreply@dsmodulesystem.com', 'DS ERP Website'))
                    ->replyTo(new Address($data['email'], $data['name']))
                    ->to('support@dsmodulesystem.com')
                    ->subject('[Contact] ' . $data['subject'])
                    ->text(sprintf(
                        "Име: %s\nИмейл: %s\n\n%s",
                        $data['name'],
                        $data['email'],
                        $data['message']
                    ));

                $this->mailService->sendSafeMail($email);
                $this->addFlash('success', 'Съобщението беше изпратено успешно.');
            } catch (\Exception) {
                $this->addFlash('error', 'Възникна грешка при изпращане. Моля, опитайте отново по-късно.');
            }

            return $this->redirect($request->getUri());
        }

        return $this->render('website/frontend/contact_page/index.html.twig', [
            'menuId' => $menuId,
            'activeMenuItem' => $this->menuService->getActiveMenuItem(),
            'form' => $form->createView(),
        ]);
    }
}
