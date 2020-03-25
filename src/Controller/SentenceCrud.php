<?php

namespace App\Controller;

use App\Form\SentenceDeleteType;
use App\Form\SentenceType;
use App\Repository\DocumentRepo;
use App\Twig\DocumentExtension;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Trismegiste\MicroWiki\Sentence;

/**
 * Sentence manager
 * 
 * @Route("/docu/show/{pk}")
 */
class SentenceCrud extends AbstractController {

    protected $repository;
    protected $logger;
    protected $csrf;

    public function __construct(DocumentRepo $repo, CsrfTokenManagerInterface $csrf, LoggerInterface $log) {
        $this->repository = $repo;
        $this->logger = $log;
        $this->csrf = $csrf;
    }

    /**
     * @Route("/append/{key}", methods={"GET","POST"})
     */
    public function append(string $pk, Request $request, string $key = ''): Response {
        $doc = $this->repository->load($pk);

        $sentence = null;
        if (strlen($key)) {
            $sentence = new Sentence($key);
        }
        $form = $this->createForm(SentenceType::class, $sentence, ['document' => $doc]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sentence = $form->getData();
            $doc->pinVertex($sentence->getKey());
            $this->repository->save($doc);

            return $this->redirectToRoute('app_documentcrud_show', ['pk' => $pk]);
        }

        return $this->render('sentence/new.html.twig', [
                    'document' => $doc,
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{key}", methods={"GET","POST"})
     */
    public function edit(string $pk, string $key, Request $request): Response {
        $doc = $this->repository->load($pk);

        $form = $this->createForm(SentenceType::class, $doc[$key], ['document' => $doc]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sentence = $form->getData();
            $doc->pinVertex($sentence->getKey());
            $this->repository->save($doc);

            return $this->redirectToRoute('app_documentcrud_show', ['pk' => $pk]);
        }

        return $this->render('sentence/edit.html.twig', [
                    'document' => $doc,
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{key}", methods={"GET","DELETE"})
     */
    public function delete(string $pk, string $key, Request $request): Response {
        $doc = $this->repository->load($pk);

        $form = $this->createForm(SentenceDeleteType::class, $doc[$key]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            unset($doc[$key]);
            $this->repository->save($doc);

            return $this->redirectToRoute('app_documentcrud_show', ['pk' => $pk]);
        }

        return $this->render('sentence/delete.html.twig', [
                    'form' => $form->createView(),
                    'key' => $key,
                    'document' => $doc,
                    'inbound' => $doc->findVertexByLink($key)
        ]);
    }

    /**
     * @Route("/link/find/{keyword}", methods={"GET"})
     */
    public function searchLinks(string $pk, string $keyword = ''): JsonResponse {
        $doc = $this->repository->load($pk);
        return $this->json($doc->searchAnyTypeOfLinksStartingBy($keyword));
    }

    /**
     * @Route("/category/find/{keyword}", methods={"GET"})
     */
    public function searchCategories(string $pk, string $keyword = ''): JsonResponse {
        $doc = $this->repository->load($pk);
        return $this->json($doc->searchCategoryStartingBy($keyword));
    }

    /**
     * @Route("/qrcode/{key}", methods={"GET"})
     */
    public function showQrCode(string $pk, string $key): Response {
        $doc = $this->repository->load($pk);
        $stc = $doc[$key];
        return $this->render('sentence/qrcode.html.twig', [
                    'document' => $doc,
                    'vertex' => $stc
        ]);
    }

    /**
     * @Route("/pin/{key}/{token}", methods={"GET"})
     */
    public function pinVertex(string $pk, string $key, string $token): Response {
        if ($this->isCsrfTokenValid(DocumentExtension::csrf, $token)) {
            $this->csrf->removeToken(DocumentExtension::csrf);
            $doc = $this->repository->load($pk);
            $doc->pinVertex($key);
            $this->repository->save($doc);
        }

        return $this->redirectToRoute('app_documentcrud_show', ['pk' => $pk]);
    }

}
