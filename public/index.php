<?php

use Carbon\Carbon;
use Symfony\Component\Dotenv\Dotenv;
use AmazonAdvertisingApi\Client;

require __DIR__.'/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$arguments = [];
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        if (false !== strpos($arg, '--')) {
            $params = explode(':', $arg);
            $param = current($params);
            $key = ltrim($param, '--');
            $value = substr($arg, strlen($param) + 1);
            $arguments['params'][$key] = $value;
        } else {
            $arguments['params'][] = $arg;
        }
    }
}

(new Cli)->handle($arguments);

class Cli
{
    protected $arguments;

    protected $params;

    private $foregroundColors = [];
    private $backgroundColors = [];

    public function __construct()
    {
        $this->foregroundColors['black'] = '0;30';
        $this->foregroundColors['dark_gray'] = '1;30';
        $this->foregroundColors['blue'] = '0;34';
        $this->foregroundColors['light_blue'] = '1;34';
        $this->foregroundColors['green'] = '0;32';
        $this->foregroundColors['light_green'] = '1;32';
        $this->foregroundColors['cyan'] = '0;36';
        $this->foregroundColors['light_cyan'] = '1;36';
        $this->foregroundColors['red'] = '0;31';
        $this->foregroundColors['light_red'] = '1;31';
        $this->foregroundColors['purple'] = '0;35';
        $this->foregroundColors['light_purple'] = '1;35';
        $this->foregroundColors['brown'] = '0;33';
        $this->foregroundColors['yellow'] = '1;33';
        $this->foregroundColors['light_gray'] = '0;37';
        $this->foregroundColors['white'] = '1;37';

        $this->backgroundColors['black'] = '40';
        $this->backgroundColors['red'] = '41';
        $this->backgroundColors['green'] = '42';
        $this->backgroundColors['yellow'] = '43';
        $this->backgroundColors['blue'] = '44';
        $this->backgroundColors['magenta'] = '45';
        $this->backgroundColors['cyan'] = '46';
        $this->backgroundColors['light_gray'] = '47';
    }

    public function handle($arguments)
    {
        $this->arguments = $arguments;
        if (!isset($this->arguments['task']) || !isset($this->arguments['action'])) {
            $this->error('No task/action called', true);
        } else {
            $task = mb_convert_case(mb_strtolower($this->arguments['task']), MB_CASE_TITLE, 'UTF-8');
            if (!class_exists($task)) {
                $this->error(sprintf('%s task does not exist', $task), true);
            }
        }

        $this->setParams();

        $task = new $task;

        if (!method_exists($task, $this->arguments['action'])) {
            $this->error(sprintf('Method %s does not exist on task %s', $this->arguments['action'], $task), true);
        }

        $response = $task->{$this->arguments['action']}($this->params);

        dd($response);
    }

    private function setParams()
    {
        if (empty($this->arguments['params'])) {
            return;
        }

        foreach ($this->arguments['params'] as $param => $value) {
            $this->params[$param] = $value;
        }
    }

    protected function hasParam($param)
    {
        return isset($this->params[$param]);
    }

    protected function getParam($param)
    {
        return $this->params[$param];
    }

    protected function setParam($param, $value)
    {
        $this->params[$param] = $value;

        return $this;
    }

    protected function prepareMessage($string)
    {
        if (is_array($string) || is_object($string)) {
            return json_encode($string);
        }

        return $string;
    }

    // Returns colored string
    public function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foregroundColors[$foregroundColor])) {
            $colored_string .= "\033[".$this->foregroundColors[$backgroundColor]."m";
        }
        // Check if given background color found
        if (isset($this->backgroundColors[$backgroundColor])) {
            $colored_string .= "\033[".$this->backgroundColors[$backgroundColor]."m";
        }

        // Add string and end coloring
        $colored_string .= $string."\033[0m";

        return $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors()
    {
        return array_keys($this->foregroundColors);
    }

    // Returns all background color names
    public function getBackgroundColors()
    {
        return array_keys($this->backgroundColors);
    }

    public function error($string, $kill = false)
    {
        echo $this->getColoredString(sprintf('%s', $this->prepareMessage($string)), 'red').PHP_EOL;

        if ($kill) {
            exit;
        }
    }

    public function success($string)
    {
        echo $this->getColoredString(sprintf('%s', $this->prepareMessage($string)), 'green').PHP_EOL;
    }

    public function info($string)
    {
        echo $this->getColoredString(sprintf('%s', $this->prepareMessage($string)), 'light_blue').PHP_EOL;
    }

    public function notification($string)
    {
        echo $this->getColoredString(sprintf('%s', $this->prepareMessage($string)), 'yellow').PHP_EOL;
    }

    public function message($string)
    {
        echo $this->prepareMessage($string).PHP_EOL;
    }
}

class Report extends Cli
{
    const CAMPAIGN_TYPE_HSA = 'headlineSearch';
    const CAMPAIGN_TYPE_SP = 'sponsoredProducts';

    /** @var Client */
    protected $client;

    protected $profileId = '2698078355751396';

    protected $metrics = [
        'bidPlus',
        'campaignName',
        'campaignId',
        'campaignStatus',
        'campaignBudget',
        'impressions',
        'clicks',
        'cost',
        'attributedConversions1d',
        'attributedConversions7d',
        'attributedConversions14d',
        'attributedConversions30d',
        'attributedConversions1dSameSKU',
        'attributedConversions7dSameSKU',
        'attributedConversions14dSameSKU',
        'attributedConversions30dSameSKU',
        'attributedUnitsOrdered1d',
        'attributedUnitsOrdered7d',
        'attributedUnitsOrdered14d',
        'attributedUnitsOrdered30d',
        'attributedSales1d',
        'attributedSales7d',
        'attributedSales14d',
        'attributedSales30d',
        'attributedSales1dSameSKU',
        'attributedSales7dSameSKU',
        'attributedSales14dSameSKU',
        'attributedSales30dSameSKU',
    ];

    protected $days = 14;

    public function __construct()
    {
        if ($this->hasParam('profileId')) {
            $this->profileId = $this->getParam('profileId');
        }

        $this->connect();

        $this->client->profileId = $this->profileId;
    }

    protected function connect()
    {
        $config = array(
            "clientId" => getenv('CLIENT_ID'),
            "clientSecret" => getenv('CLIENT_SECRET'),
            "refreshToken" => getenv('REFRESH_TOKEN'),
            "region" => getenv('REGION'),
            "sandbox" => (bool)getenv('SANDBOX'),
        );

        try {
            $this->client = new Client($config);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    private function csv($data, $header = null, $fileName = 'campaign')
    {
        $fileName = sprintf('%s_%s.csv', $fileName, time());
        if (!$handle = fopen(__DIR__.'/'.$fileName, 'w')) {
            $this->error(sprintf('Could not create file %s', $fileName), true);
        }
        $header = $header ?? array_keys($data[0]);
        $ret = fputcsv($handle, $header, ',', '"');

        foreach ($data as $k => $value) {
            $preparedRow = [];
            foreach ($header as $key) {
                if (!isset($value[$key])) {
                    $preparedRow[$key] = '';
                } else {
                    $preparedRow[$key] = $value[$key];
                }
            }

            $ret &= fputcsv($handle, $preparedRow, ',', '"');
        }

        return $ret;
    }

    public function profiles()
    {
        return $this->client->listProfiles();
    }

    public function requestReport()
    {
        return $this->client->requestReport(
            'campaigns',
            [
                'campaignType' => self::CAMPAIGN_TYPE_HSA,
                'reportDate' => Carbon::now()->subDays($this->days)->format('Ymd'),
                'metrics' => implode(',', $this->metrics),
            ]
        );
    }

    public function getReport($params)
    {
        return $this->client->getReport($params['id']);
    }

    public function getReportCsv($params)
    {
        $response = $this->client->getReport($params['id']);
        $report = json_decode($response['response'], true);

        return $this->csv($report, $this->metrics);
    }
}

