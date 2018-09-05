<?php

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