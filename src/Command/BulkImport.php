<?php

/*
 * AppMicroWiki
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trismegiste\MicroWiki\Document;
use Trismegiste\MicroWiki\Sentence;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Import an OpenOffice file
 */
class BulkImport extends Command
{

    const allcell = "//*[name()='office:spreadsheet']/*[name()='table:table']/*[name()='table:table-row']/*[name()='table:table-cell']/*[name()='text:p']";
    const allrow = "//*[name()='office:spreadsheet']/*[name()='table:table']/*[name()='table:table-row']";

    protected static $defaultName = 'app:import';
    private $repository;

    public function __construct(Repository $documentRepo)
    {
        $this->repository = $documentRepo;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import an Open Office into a Document')
            ->addArgument('filename', InputArgument::REQUIRED, 'XML filename');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());


        $xml = new \DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->load($input->getArgument('filename'));
        $search = new \DOMXPath($xml);
        $iter = $search->query(self::allrow);
        $newDoc = new Document();
        foreach ($iter as $node) {
            $vertex = new Sentence($node->childNodes->item(0)->nodeValue);
            $vertex->setCategory($node->childNodes->item(1)->nodeValue);
            $vertex->setContent($node->childNodes->item(2)->nodeValue);
            $newDoc[] = $vertex;
        }

        $io->section('Importing ' . $newDoc->count() . ' vertices');
        $newDoc->setTitle($io->ask('Name of the new document'));
        $newDoc->setDescription($io->ask('Description of the new document'));
        $this->repository->save($newDoc);
        $io->success($newDoc->getTitle() . ' successfully saved');

        return 0;
    }

}
