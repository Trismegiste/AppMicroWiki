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
 * 
 * @Route("/docu/show/{title}")
 */
class SentenceCrud extends AbstractController {

    protected $repository;
    protected $logger;

    public function __construct(DocumentFactory $repo, LoggerInterface $log) {
        $this->repository = $repo;
        $this->logger = $log;
    }

    /**
     * @Route("/append/{key}", methods={"GET","POST"})
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
     * @Route("/edit/{key}", methods={"GET","POST"})
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
     * @Route("/delete/{key}", methods={"GET","DELETE"})
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
                    'document' => $doc,
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
     * @Route("/link/find/{keyword}", methods={"GET"})
     */
    public function searchLinks(string $title, string $keyword = '') {
        $doc = $this->repository->load($title);
        return $this->json($doc->searchAnyTypeOfLinksStartingBy($keyword));
    }

    /**
     * @Route("/category/find/{keyword}", methods={"GET"})
     */
    public function searchCategories(string $title, string $keyword = '') {
        $doc = $this->repository->load($title);
        return $this->json($doc->searchCategoryStartingBy($keyword));
    }

    /**
     * @Route("/qrcode/{key}", methods={"GET"})
     */
    public function showQrCode(string $title, string $key = '') {
        $doc = $this->repository->load($title);
        $stc = $doc[$key];
        return $this->render('sentence/qrcode.html.twig', [
                    'document' => $doc,
                    'vertex' => $stc
        ]);
    }

}
