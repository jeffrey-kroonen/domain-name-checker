<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DomainCheckCommand extends Command
{
    protected static $defaultName = 'app:domain-check';

    /**
     * @var Symfony\Component\HttpClient\HttpClient
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $targetFile;

    /**
     * @var string
     */
    private $resultFile;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

        $this->baseUrl = 'https://www.versio.nl/api/v1';

        $this->targetFile = dirname(__DIR__, 2) . '/public/target/check-domains.csv';

        $this->resultFile = dirname(__DIR__, 2) . '/public/target/result-check-domains.csv';

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Check domains from target file on availibity.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * Check if given file is a real file.
         */
        if (!file_exists($this->targetFile)) {
            $io->writeln(sprintf('<error>The target file string isn\'t a file. Given path: %s</error>', $this->targetFile));
            return Command::FAILURE;
        }

        /**
         * Open file stream.
         */
        $fileStream = fopen($this->targetFile, 'r');

        /**
         * The list of top level domain names what. This list will be checked on availability.
         */
        $targetTopDomains = [
            '.nl',
            '.com'
        ];

        $resultSet = [];

        $header = true;

        /**
         * Process domains given in file.
         */
        while (($data = fgetcsv($fileStream, 1000, ',')) !== FALSE) {

            if ($header == true) {
                $header = false;
            } else {

                foreach ($targetTopDomains as $targetTopDomain) {

                    $endpoint = $this->baseUrl . '/domains/' . trim(strtolower($data[0])) . $targetTopDomain . '/availability';
                    /**
                     * Http request to versio
                     */
                    $res = $this->client->request('GET', $endpoint, [
                            'auth_basic' => [$_ENV['VERSIO_USERNAME'], $_ENV['VERSIO_PASSWORD']]
                        ]
                    );

                    /**
                     * Set result set.
                     */
                    if ($res->toArray()['available']) {
                        $result = 'beschikbaar';
                    } else {
                        $result = 'niet beschikbaar';
                    }

                    $resultSet[] = [
                        (trim(strtolower($data[0])) . $targetTopDomain),
                        $result
                    ];

                }

            }

        }
        fclose($fileStream);

        /**
         * Save result set to file.
         */
        $fileStream = fopen($this->resultFile, 'w');

        fputcsv($fileStream, ['domainnaam', 'beschikbaarheid']);

        foreach ($resultSet as $check) {
            fputcsv($fileStream, $check);
        }

        fclose($fileStream);

        $io->success(sprintf('All checks has been done. Please check the file at the given path: %s', $this->targetFile));

        return Command::SUCCESS;
    }
}
