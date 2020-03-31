<?php

namespace App\Controller;

use App\Form\DocumentType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Trismegiste\MicroWiki\Document;
use Trismegiste\Toolbox\Iterator\ClosureDecorator;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Document manager
 * 
 * @Route("/docu")
 */
class DocumentCrud extends AbstractController
{

    protected $repository;
    protected $logger;

    public function __construct(Repository $documentRepo, LoggerInterface $log)
    {
        $this->repository = $documentRepo;
        $this->logger = $log;
    }

    /**
     * @Route("/list", methods={"GET"})
     */
    public function list(): Response
    {
        $iter = $this->repository->search();

        return $this->render('document/list.html.twig', [
                'listing' => new ClosureDecorator($iter, function(Document $docu) {
                        return (object) [
                                'pk' => $docu->getPk(),
                                'title' => $docu->getTitle(),
                                'description' => $docu->getDescription(),
                                'vertex' => count($docu)
                        ];
                    })
        ]);
    }

    /**
     * @Route("/new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $form = $this->createForm(DocumentType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $docu = $form->getData();
            $this->repository->save($docu);

            return $this->redirectToRoute('app_documentcrud_show', ['pk' => $docu->getPk()]);
        }

        return $this->render('document/new.html.twig', [
                'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{pk<[0-9a-f]{24}>}", methods={"GET"})
     */
    public function show(string $pk): Response
    {
        try {
            $doc = $this->repository->load($pk);
        } catch (\Exception $ex) {
            throw $this->createNotFoundException("The Document $pk was not found", $ex);
        }

        return $this->render('document/show.html.twig', ['document' => $doc, 'listing' => $doc->getIterator()]);
    }

}
