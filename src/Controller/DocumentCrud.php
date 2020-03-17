<?php

// src/Controller/LuckyController.php

namespace App\Controller;

use App\Repository\DocumentFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DocumentCrud extends AbstractController {

    protected $repository;

    public function __construct(DocumentFactory $repo) {
        $this->repository = $repo;
    }

    /**
     * @Route("/show")
     */
    public function show() {
        $doc = $this->repository->create();
        $doc->setTitle('Yolo');

        return $this->render('crud/show.html.twig', [
                    'doc' => $doc,
        ]);
    }

}
