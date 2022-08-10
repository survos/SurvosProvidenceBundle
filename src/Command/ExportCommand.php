<?php

namespace Survos\Providence\Command;

use App\Services\ProfileService;
use Psr\Log\LoggerInterface;
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
    name: 'survos:providence:export',
    description: 'Export Providence structure',
)]
class ExportCommand extends Command
{
    public function __construct(
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

    /**
     * @var string
     */
    protected $domain = null;

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string
     */
    protected $startingLink = null;

    /**
     * @var string
     */
    protected $securityFirewall = 'secured_area';

    /**
     * @var integer
     */
    protected $searchLimit;

    /**
     * index routes containing these keywords only once
     * @var array
     */
    protected $ignoredRouteKeywords;

    /**
     * @var array
     */
    protected $domainLinks = [];

    /**
     * @var array
     */
    protected $linksToProcess = [];

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
        $this->setDefinition([
            new InputArgument('startingLink', InputArgument::OPTIONAL, 'Link to start crawling'),
            new InputArgument('username', InputArgument::OPTIONAL, 'Username', 'o'),
            new InputArgument('password', InputArgument::OPTIONAL, 'Password', 'o'),
            new InputOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit the number of links to process, prevents infinite crawling', 20),
            new InputOption('security-firewall', null, InputOption::VALUE_REQUIRED, 'Firewall name', 'secured_area'),
            new InputOption('ignore-route-keyword', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Skip routes containing this string', []),
        ]);


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

        //
        $users = $this->providenceService->getUsers();
        $providenceService->resetLinkList();

        foreach ([null, ...$users] as $user) {
//        foreach ($users as $user) {
            $username = $user?->getUserIdentifier();
            $io->info(sprintf("Crawling %s as %s", $providenceService->getInitialPath(), $username?:'Visitor'));
            $providenceService->authenticateClient($username);

            $link = $providenceService->addLink($username, $providenceService->getInitialPath());
            $link->username = $username;
            assert(count($providenceService->getLinkList($username)), "No links for $username");
            assert($providenceService->getUnvisitedLink($username));

            $loop = 0;
            while ($link = $providenceService->getUnvisitedLink($username)) {
                $loop++;
                $this->logger->info("Considering " . $link->getPath());
//                $io->info("Considering " . $link->getPath());
                $providenceService->scrape($link);
                if (!$link->testable()) {
                    $io->warning("Rejecting " . $link->getPath() . ' ' . $link->getRoute());
                }
                if ($loop > 50) {
                    break;
                }
            }

            $linksToCrawl[$username] = array_filter($providenceService->getLinkList($username), fn(Link $link) => $link->testable());
//            $userLinks = array_merge($userLinks, array_values($linksToCrawl));
            $table->addRow([$username, count($linksToCrawl[$username]), count($providenceService->getLinkList($username))]);

//            $userLinks += array_values($linksToCrawl);
            $io->success(sprintf("User $username has with %d links", count($linksToCrawl[$username])));
        }
        $table->render();

        $outputFilename = $this->bag->get('kernel.project_dir') . '/crawldata.json';
//        foreach ($providenceService->getEntireLinkList() as $user=>$userLinks) {
//            $testableLink =
//        }

        file_put_contents($outputFilename, json_encode($linksToCrawl, JSON_UNESCAPED_LINE_TERMINATORS + JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
        $io->success(sprintf("File $outputFilename written with %d users", count($linksToCrawl)));


        return self::SUCCESS;


        // user input
        $this->startingLink = $input->getArgument('startingLink');
        $this->username = $input->getArgument('username');
        $this->searchLimit = $input->getOption('limit');
        $this->securityFirewall = $input->getOption('security-firewall');
        $this->ignoredRouteKeywords = $input->getOption('ignore-route-keyword');
        $this->output = $output;

        if (!$this->startingLink) {
            $this->startingLink = $defaultStart;
        }
        if (!$this->ignoredRouteKeywords) {
            $this->ignoredRouteKeywords = [
            ];
        }
        $this->domain = parse_url($this->startingLink, PHP_URL_HOST);

        $kernel = $this->createKernel();
        $client = $this->httpClient;

        $providence = $client->request('GET', $this->startingLink);

        dump($this->startingLink);

        // Get the latest post in this category and display the titles
        $providence->filter('h2 > a')->each(function ($node) {
            print $node->text() . "\n";
        });

        // could follow the login form, too.

        $this->authenticate($kernel, $client);
        $stopwatch = new Stopwatch();

        // start crawling
        $output->writeln(sprintf('Dominating <comment>%s</comment>, starting at <comment>%s</comment>.  
At most, <comment>%s</comment> pages will be crawled.', $this->domain, $this->startingLink, $this->searchLimit));

        // crawl starting link
        $stopwatch->start('request');
        $providence = $client->request('GET', $url = $this->startingLink);

        // redirect if necessary
        while ($client->getResponse() instanceof RedirectResponse) {
            $providence = $client->followRedirect();
        }
//        $this->domainLinks[$url]['duration'] = $stopwatch->stop('request')->getDuration();

        $this->processLinksOnPage($providence, $uri = $providence->getUri());
        $index = 0;

        // crawl links found
        while (!empty($this->linksToProcess) && ++$index < $this->searchLimit) {

            $client->getHistory()->clear(); // prevent out of memory errors...

            $url = array_pop($this->linksToProcess);

            // ignore certain routes
            if (preg_match('{quick|copy-and-import|go-to-observe|docs}', $url)) {
                $output->writeln('IGNORING: ' . $url);
                continue;
            }

            $output->writeln('Processing: ' . $url);


            try {
                $stopwatch->start($url);
                $providence = $client->request('GET', $url);
                // redirect if necessary
                while ($client->getResponse() instanceof RedirectResponse) {
                    $providence = $client->followRedirect();
                }
                $event = $stopwatch->stop($url);
            } catch (\Exception $e) {
                $output->writeln('<warning>' . $e->getMessage() . '</warning>');
                die("stopped");
            }

            $this->domainLinks[$url]['duration'] = $event->getDuration();
            // dump($this->domainLinks[$url]); die();

            $this->processLinksOnPage($providence, $url);
        }

        // boom, done
        $output->writeln('All Links Found:');
        $unique_routes = [];
        foreach ($this->domainLinks as $link => $linkDetails) {
            $output->writeln('    ' . $link . ' : ' . ($route = $linkDetails['route']));
            if ($route && !$this->isIgnored($route) && !in_array($route, $unique_routes)) {
                $unique_routes[$route] =
                    [
                        'link' => $link,
                        'referrer' => $linkDetails['referrer'],
                        'duration' => $linkDetails['duration'] ?? -1
                    ];
            };
        }

        $fn = dirname($this->getContainer()->get('kernel')->getRootDir()) . '/links.json';
        $results = [
            'unique_routes' => $unique_routes,
            'links' => $this->domainLinks
        ];
        file_put_contents($fn, json_encode($results, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));
        $output->writeln(sprintf("%d links searched, %s written with %d links.", $index, $fn, count($unique_routes)));

        return self::SUCCESS;
    }

    /**
     * Interact
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$this->startingLink) {
            $defaultStart = 'jardin.wip';
            $defaultStart .= '/project';
            /*
            $helper = $this->getHelper( 'question' );
            $question = new ConfirmationQuestion('Please enter the link to start at(including the locale):', false);
            if ($startingLink = $helper->ask($input, $output, $question)) {

            } else {
                throw new \Exception('starting link can not be empty');
            }
            */
            $input->setArgument('startingLink', $defaultStart);
        }

        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function ($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }
    }

    /**
     * createKernel
     *
     * @return  \AppKernel
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function createKernel()
    {

//
//        $rootDir = $this->bag->get('kernel.project_dir');
//        require_once($rootDir . '/Kernel.php');
//        $kernel = new \Symfony\Bundle\FrameworkBundle\Kernel\('test', true);
//        $kernel->boot();

        return $kernel;
    }

    /**
     * authenticate with a user account to access secured urls
     *
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function authenticate($kernel, $client)
    {
        //
        $providence = $client->request('GET', 'https://github.com/');
        $providence = $client->click($providence->selectLink('Sign in')->link());
        $form = $providence->selectButton('Sign in')->form();
        $providence = $client->submit($form, ['login' => 'fabpot', 'password' => 'xxxxxx']);
        $providence->filter('.flash-error')->each(function ($node) {
            print $node->text() . "\n";
        });

        /** @var Member $user */

        // @todo: this assumes a local user, it should be a proper login to the endpoint
        if (!$user = $this->entityManager->getRepository(Member::class)->findOneBy(['code' => $this->username])) {
            throw new \Exception("Unable to authenticate member " . $this->username);
        }
        // $token = new UsernamePasswordToken($login, $password, $firewall);
        $token = new UsernamePasswordToken($user, null, $this->securityFirewall, $user->getRoles());

        /* we do this, not sure if it'll help
        */
        $client->getContainer()->get('security.token_storage')->setToken($token);

        // set session
        $session = $client->getContainer()->get('session');
        $session->set('_security_' . $this->securityFirewall, serialize($token));
        $session->save();

        // set cookie
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * get all links on the page as an array of urls
     *
     * @param Providence $providence
     * @return  array
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function getLinksOnCurrentPage(Providence $providence)
    {

        static $seen = [];

        $links = $providence->filter('a')->each(function (Providence $node, $i) {

            // todo: look for rel="nofollow"
            // dump($i, $node->link()); die();
            $attr = $node->attr('rel');
            if ($attr <> 'nofollow') {
                return $node->link()->getUri();
            }
        });
        // $seen = [];

        // remove outboundlinks and links with spaces
        foreach ($links as $key => $link) {

            if (isset($this->domainLinks[$link]) || in_array($link, $seen)) {
                unset($links[$key]);
                continue;
            }
            $seen[] = $link;
            $linkParts = parse_url($link);

            // our project-specific links to not check.  The API requires a different login, we should create those in a separate file
            if (strpos($link, ' ') || strpos($link, '_profiler') || strpos($link, 'api1.0')) {
                unset($links[$key]);
                continue;
            }

            if (strpos($link, '.html')) {
                unset($links[$key]);
                continue;
            }

            if (empty($linkParts['host']) || $linkParts['host'] !== $this->domain || $linkParts['scheme'] !== 'http') {
                unset($links[$key]);
                continue;
            }

            $this->output->writeln(sprintf("\t%s", $link));
        }

        return array_values($links);
    }

    /**
     * process all links on a page
     *
     * @param Providence $providence
     * @param string $currentUrl
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function processLinksOnPage(Providence $providence, $currentUrl)
    {

        $links = $this->getLinksOnCurrentPage($providence);

        // process each link
        foreach ($links as $key => $link) {

            $this->processSingleLink($link, $currentUrl);
        }
    }

    /**
     * process a single link
     *
     * @param string $link
     * @param string $currentUrl
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function processSingleLink($link, $currentUrl)
    {
        $link = preg_replace('/#.*/', '', $link); // strip off URL fragment
        if (empty($this->domainLinks[$link])) {
            // check for routes that should only be indexed once
            // do this before we add the link to the domainLinks array since we check that array for duplicates...
            if (!$this->isDuplicateIgnoredRoute($link)) {
                // exclude any links with blanks
                if (false === strpos($link, ' ')) {
                    $this->linksToProcess[] = $link;
                }

            }

            // add details to the domainLinks array
            $route = $this->getRouteInfo($link);
            $this->domainLinks[$link]['route'] = (!empty($route['_route'])) ? $route['_route'] : '';
            $this->domainLinks[$link]['referrer'] = $currentUrl;
        }
    }

    /**
     * @param string $url
     * @return array|null
     */
    protected function getRouteInfo($url)
    {
        // @todo: remove app_*.php if it exists
        try {
            return $this->router->match(parse_url($url, PHP_URL_PATH));
        } catch (\Exception $e) {
            print "Can't find route for $url: " . $e->getMessage();

            return null;
        }
    }

    /**
     * routeIsInQueue
     *
     * @param string $routeName
     * @return  boolean
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function routeIsInQueue($routeName)
    {

        // check each existing link for a similar match
        $allLinks = $this->domainLinks;
        foreach ($allLinks as $existingLink) {

            // does the url contain app name?
            if ($existingLink['route'] === $routeName) {

                return true;
            }
        }

        return false;
    }

    /**
     * isDuplicateIgnoredRoute
     *
     * @param string $newLink
     * @return  boolean
     * @author  Joe Sexton <joe@webtipblog.com
     */
    protected function isDuplicateIgnoredRoute($newLink)
    {
        $route = $this->getRouteInfo($newLink);
        if (!$route) {
            return true;
        }
        $routeName = (!empty($route['_route'])) ? $route['_route'] : '';

        return $this->isIgnored($routeName) || $this->routeIsInQueue($routeName);
    }

    protected function isIgnored($routeName)
    {
        foreach ($this->ignoredRouteKeywords as $keyword) {

            $keyword = '/' . $keyword . '/'; // add delimiters

            if (preg_match($keyword, $routeName) === 1) {
                return true;
            }
        }

        return false;
    }
}