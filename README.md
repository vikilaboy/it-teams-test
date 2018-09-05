**Amazon Advertising Api - IT TEAMS Test**

This is a PHP CLI App

The code will get a generated report or you can generate another one.
Based on ``profileId`` param you can generate another report.

Steps to follow: 

run ``composer install``

Setup .env file with credentials. See .env.example file.

By default, there is a default profileId but you can override it by using the ``--profileId`` param

``php bin report profiles`` - will return available profiles

``php bin requestReport [--profileId:]`` - will send a request to Amazon to generate a report for you. The report request contains all metrics available for the default profile
if you want to request a report for a different profile use ``--profileId`` param.
The command fil return a reportId which will be used, after Amazon finish to generate the report.

``php bin getReport --id:report-id-from-upper-request`` this will return the report values on the screen

``php bin getReportCsv --id:report-id-from-upper-request`` - this will make the csv file with values from report. the csv file is available on ``public`` folder   

For a cronjob we can use 

``40       3       *      *       *    php -q -f bin requestReport >> /dev/null 2>&1``

There's also a docker configuration but you will need a load balancer. I used traefik. Check .docker folder and docker-compose.yml



