<?php

namespace App\Controller;

use App\Form\SentenceDeleteType;
use App\Form\SentenceType;
use App\Repository\DocumentFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\MicroWiki\Sentence;
use Trismegiste\MicroWiki\Document;

/**
 * Sentence manager
 */
class SentenceCrud extends AbstractController {

    protected $repository;
    protected $logger;

    public function __construct(DocumentFactory $repo, LoggerInterface $log) {
        $this->repository = $repo;
        $this->logger = $log;
    }

    /**
     * @Route("/vertex/append/{title}/{key}", methods={"GET","POST"})
     */
    public function append(string $title, Request $request, string $key = '') {
        $doc = $this->repository->load($title);

        $sentence = null;
        if (strlen($key)) {
            $sentence = new Sentence($key);
        }
        $form = $this->createForm(SentenceType::class, $sentence, ['document' => $doc]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sentence = $form->getData();
            $this->repository->save($doc);

            return $this->redirectToShowVertex($doc, $sentence);
        }

        return $this->render('sentence/new.html.twig', [
                    'document' => $doc,
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/vertex/edit/{title}/{key}", methods={"GET","POST"})
     */
    public function edit(string $title, Request $request, string $key) {
        $doc = $this->repository->load($title);

        $form = $this->createForm(SentenceType::class, $doc[$key], ['document' => $doc]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sentence = $form->getData();
            $this->repository->save($doc);

            return $this->redirectToShowVertex($doc, $sentence);
        }

        return $this->render('sentence/edit.html.twig', [
                    'document' => $doc,
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/vertex/delete/{title}/{key}", methods={"GET","DELETE"})
     */
    public function delete(string $title, string $key, Request $request): Response {
        $doc = $this->repository->load($title);

        $form = $this->createForm(SentenceDeleteType::class, $doc[$key]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            unset($doc[$key]);
            $this->repository->save($doc);

            return $this->redirectToRoute('app_documentcrud_show', ['title' => $doc->getTitle()]);
        }

        return $this->render('sentence/delete.html.twig', [
                    'form' => $form->createView(),
                    'key' => $key,
                    'doc' => $doc->getTitle(),
                    'inbound' => $doc->findVertexByLink($key)
        ]);
    }

    protected function redirectToShowVertex(Document $doc, Sentence $sentence): Response {
        return $this->redirectToRoute('app_documentcrud_show', [
                    'title' => $doc->getTitle(),
                    'key' => $sentence->getKey(),
        ]);
    }

    /**
     * @Route("/vertex/find/{title}/{keyword}", methods={"GET"})
     */
    public function searchLinks(string $title, string $keyword = '') {
        $doc = $this->repository->load($title);
        return $this->json($doc->searchAnyTypeOfLinksStartingBy($keyword));
    }

}
