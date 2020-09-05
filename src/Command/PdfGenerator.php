<?php

/*
 * AppMicroWiki
 */

namespace App\Command;

use Dompdf\Dompdf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;
use Trismegiste\Toolbox\MongoDb\Repository;
use Twig\Environment;

/**
 * Description of PdfGenerator
 */
class PdfGenerator extends Command
{

    protected static $defaultName = 'pdf:generate';
    private $repository;
    private $twig;

    public function __construct(Repository $documentRepo, Environment $twig)
    {
        $this->repository = $documentRepo;
        $this->twig = $twig;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate a PDF from a Document')
            ->addArgument('filename', InputArgument::REQUIRED, 'PDF filename');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $listing = iterator_to_array($this->repository->search([], ['vertex']));
        $view = array_map(function(Document $doc) {
            return $doc->getTitle();
        }, $listing);

        $name = $io->choice("Choose your document to export", $view);

        $found = $this->repository->search(['title' => $name]);
        $found->rewind();
        $doc = $found->current();

        $vertex = iterator_to_array($doc->getIterator());
        usort($vertex, function(Sentence $a, Sentence $b) {
            return strcmp($a->getKey(), $b->getKey());
        });

        $html = $this->twig->render('document/pdf.html.twig', ['document' => $doc, 'listing' => $vertex]);
        $generator = new Dompdf();
        $generator->loadHtml($html);
        $generator->render();
        file_put_contents($input->getArgument('filename'), $generator->output());

        return 0;
    }

}
