<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Miloske85\php_cli_table\Table as CliTable;
use DateTime;
use Exception;
use Symfony\Component\Yaml\Yaml;

class UptimeRobot extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create:uptimerobot 
        {--domain= : The domain to be monitored}
        {--settings=/etc/parallax/settings.yaml : The settings.yml file to use}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Adds a site to UptimeRobot for HTTP(S) monitoring.';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Our function
        // Load in settings
        $this->settings = Yaml::parseFile($this->option('settings'));
        $urApiKey = $this->settings['uptimerobot']['apiKeyMain'];

        // get the list of current sites in UptimeRobot
        $request = 'api_key=' . $urApiKey . '&format=json';
        $curl = curl_init();
        curl_setopt_array($curl, array (
            CURLOPT_URL            => 'https://api.uptimerobot.com/v2/getMonitors',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $request,
            CURLOPT_HTTPHEADER     => array (
                'cache-control: no-cache',
                'content-type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $response = json_decode($response);
        $response = $response->monitors;
        $i = 0;
        foreach($response as $site) {
            $i++;
            $site->url = preg_replace("(^https?://)", "", $site->url );
            $site->url = str_replace("/", "", $site->url);
            $urList[$i] = $site->url;
        }
        print_r($urList);
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}


?>