<?php

namespace App\Controller;

use App\Entity\GraphSpeech\Sentence;
use App\Form\DocumentType;
use App\Repository\DocumentFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Document manager
 */
class DocumentCrud extends AbstractController {

    protected $repository;

    public function __construct(DocumentFactory $repo) {
        $this->repository = $repo;
    }

    /**
     * @Route("/docu/list")
     */
    public function list() {
        return $this->render('crud/list.html.twig', [
                    'listing' => $this->repository->list()
        ]);
    }

    /**
     * @Route("/docu/new")
     */
    public function new(Request $request) {
        $form = $this->createForm(DocumentType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $docu = $form->getData();
            $this->repository->save($docu);

            return $this->redirectToRoute('docu_show', ['title' => $docu->getTitle()]);
        }

        return $this->render('crud/new.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("docu/show/{title}")
     */
    public function show($title) {
        $doc = $this->repository->create();
        $doc->setTitle('Yolo');
        $obj = new Sentence($doc, 'Alice');
        $doc->setDescription('Some Mind Map');
        $obj->setContent('She owns [[artefact]] and she knows [[Caterpillar]]');

        return $this->render('crud/show.html.twig', [
                    'doc' => $doc,
        ]);
    }

}
