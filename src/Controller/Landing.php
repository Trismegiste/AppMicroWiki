<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Landing page
 */
class Landing extends AbstractController {

    /**
     * @Route("/", methods={"GET"})
     */
    public function home() {
        return $this->redirectToRoute('app_documentcrud_list');
    }

}
