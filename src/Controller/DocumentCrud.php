<?php

namespace App\Controller;

use App\Entity\GraphSpeech\Document;
use App\Entity\GraphSpeech\Sentence;
use App\Form\DocumentType;
use App\Form\SentenceDeleteType;
use App\Form\SentenceType;
use App\Repository\DocumentFactory;
use Psr\Log\LoggerInterface;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
                    'listing' => array_map(function(SplFileInfo $doc) {
                                return $doc->getBasename('.json');
                            },
                            \iterator_to_array($this->repository->list()))
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
        if (($key !== '') && (!$doc->offsetExists($key))) {
            return $this->redirectToRoute('app_documentcrud_append', ['title' => $title, 'key' => $key]);
        }

        return $this->render('document/show.html.twig', [
                    'doc' => $doc,
                    'focus' => $key
        ]);
    }

}
