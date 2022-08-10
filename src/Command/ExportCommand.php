<?php

namespace Survos\Providence\Command;

use Psr\Log\LoggerInterface;

use Survos\Providence\Services\ProfileService;
use Survos\Providence\Services\ProvidenceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'survos:providence:analyze',
    description: 'Export Providence structure',
)]
class ExportCommand extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private ProfileService $profileService,
        private ProvidenceService         $providenceService,
        string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @var OutputInterface
     */
    protected $output;


    protected function configure(): void
    {
        $this
            ->addArgument('filename', InputArgument::OPTIONAL, 'Path to <profile>.xml file', __DIR__ . './../../xml/demo.xml')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');

    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @author  Joe Sexton <joe@webtipblog.com
     * @todo    use product sitemap to crawl product pages
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = new Table($output);
        $table
            ->setHeaders(['User', '#Testable', '#Found']);

        $providenceService = $this->providenceService;
        $xml = file_get_contents($input->getArgument('filename'));
        $xmlProfile = $this->profileService->parseXml($xml);

        $io->success(sprintf("Profile %s loaded.", $xmlProfile->code));

        return self::SUCCESS;

    }

}
