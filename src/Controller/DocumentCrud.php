<?php

namespace App\Controller;

use App\Form\DocumentType;
use App\Repository\DocumentFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Document manager
 */
class DocumentCrud extends AbstractController {

    protected $repository;
    protected $logger;

    public function __construct(DocumentFactory $repo, LoggerInterface $log) {
        $this->repository = $repo;
        $this->logger = $log;
    }

    /**
     * @Route("/docu/list", methods={"GET"})
     */
    public function list() {
        return $this->render('document/list.html.twig', [
                    'listing' => $this->repository->list()
        ]);
    }

    /**
     * @Route("/docu/new", methods={"GET","POST"})
     */
    public function new(Request $request) {
        $form = $this->createForm(DocumentType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $docu = $form->getData();
            $this->repository->save($docu);

            return $this->redirectToRoute('app_documentcrud_show', ['title' => $docu->getTitle()]);
        }

        return $this->render('document/new.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/docu/show/{title}/{key}", methods={"GET"})
     */
    public function show(string $title, string $key = '') {
        $doc = $this->repository->load($title);
        if ($key !== '') {
            $doc->pinVertex($key);
            $this->repository->save($doc);
        }

        if (($key !== '') && (!$doc->offsetExists($key))) {
            return $this->redirectToRoute('app_sentencecrud_append', ['title' => $title, 'key' => $key]);
        }

        return $this->render('document/show.html.twig', [
                    'document' => $doc,
                    'focus' => $key
        ]);
    }

}
