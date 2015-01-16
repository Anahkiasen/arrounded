<?php
namespace Arrounded\Commands;

use Arrounded\Testing\Crawler;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Console\Input\InputOption;

class BenchQueries extends Command
{
    /**
     * The console command name.
     *
     * @type string
     */
    protected $name = 'arrounded:bench';

    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Bench queries per page and list the biggest offenders';

    /**
     * The pages to ignore
     *
     * @type array
     */
    protected $ignored = [];

    /**
     * Log of the queries
     *
     * @type array
     */
    protected $queries = [];

    /**
     * The user to authenticate as
     *
     * @type integer
     */
    protected $user = 1;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $tableBuilder = $this->getHelperSet()->get('table');

        // Build summary
        $table = array();
        $this->getQueries();
        foreach ($this->queries as $route => $log) {
            $table[$route] = array(
                $route,
                count($log['queries']),
            );
        }

        // Sort by number of queries
        $table = array_sort($table, function ($entry) {
            return $entry[1] * -1;
        });

        // Filter out low-query pages
        $table = array_filter($table, function ($entry) {
            return $entry[1] > 10;
        });

        // Render
        $tableBuilder->setHeaders(['Route', 'Queries'])->setRows($table);
        $tableBuilder->render($this->getOutput());

        if ($route = $this->option('route')) {
            $route = $this->laravel['url']->to($route);
            echo $this->queries[$route]['response'];
            print_r($this->queries[$route]['queries']);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('route', 'r', InputOption::VALUE_REQUIRED, 'A route to display queries for'),
        );
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get all queries by route
     */
    protected function getQueries()
    {
        $crawler = $this->getCrawler();

        // Spoof Redirect::back
        $endpoint = Redirect::home();
        Redirect::shouldReceive('to')->andReturn($endpoint);
        Redirect::shouldReceive('back')->andReturn($endpoint);

        // Create Client
        $this->laravel['auth']->loginUsingId($this->user);
        $client = new Client($this->laravel, array());

        // Crawl routes
        $routes = $crawler->getRoutes();
        $this->info('Found '.count($routes).' routes');
        foreach ($routes as $route) {
            $this->inspect($client, $route);
        }
    }

    /**
     * Inspect a route
     *
     * @param Client $client
     * @param string $route
     */
    protected function inspect(Client $client, $route)
    {
        try {
            $this->info('Inspecting '.$route);
            $client->request('GET', $route);
        } catch (Exception $exception) {
            $this->error('Error inspecting '.$route);
        }

        // Format and sort queries
        $routeQueries = DB::getQueryLog();
        $routeQueries = array_pluck($routeQueries, 'query');
        sort($routeQueries);

        // Cancel if no queries on this page
        if (empty($routeQueries)) {
            return;
        }

        // Store and flush
        $this->queries[$route]['response'] = $client->getResponse()->getContent();
        $this->queries[$route]['queries']  = $routeQueries;
        DB::flushQueryLog();
    }

    /**
     * Get the routes to ignore
     *
     * @return array
     */
    protected function getIgnoredRoutes()
    {
        return array_merge(array(), $this->ignored);
    }

    /**
     * @return Crawler
     */
    protected function getCrawler()
    {
        $crawler = new Crawler($this->laravel);
        $crawler->setIgnored($this->getIgnoredRoutes());

        return $crawler;
    }
}
