<?php

use Carbon\Carbon;
use AmazonAdvertisingApi\Client;

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
        if (!$handle = fopen(BASE_PATH.'/public/'.$fileName, 'w')) {
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

        if ($ret) {
            return $fileName;
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