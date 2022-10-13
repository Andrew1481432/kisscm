<?php
/**
 * ДЗ №2. Визуализатор зависимостей пакета
 * Написать на выбранном вами языке программирования программу,
 *
 * которая принимает в качестве аргумента командной строки имя пакета, а возвращает граф
 *
 * его зависимостей в виде текста на языке Graphviz. На выбор: для npm или для pip. Пользоваться
 * самими этими менеджерами пакетов запрещено. Главное, чтобы программа работала даже с неустановленными
 * пакетами и без использования pip/npm.
 *
 * @author testerdev
 */

include_once 'vendor/include_all.php';

use manager\{PacketManager, NpmManager};

class Loader {

    public const DEBUG_MODE = false;

    /** @var PacketManager|null  */
    private $packetManager = null;

    function __construct() {
        // TODO
        $this->packetManager = new NpmManager();
    }

    private function fillDigraph(string $parentName, array $dependencies, &$graphvizArr) {
        foreach ($dependencies as $key => $val) {
            $graphvizArr[$parentName][] = $key;
            if($val) {
                $this->fillDigraph($key, $val, $graphvizArr);
            }
        }
    }

    private function showDigraph(array $graphvizArr) {
        echo "digraph G {".PHP_EOL;
        foreach ($graphvizArr as $parentName => $dependencies) {
            foreach ($dependencies as $dependency) {
                echo "\"$parentName\" -> \"$dependency\";".PHP_EOL;
            }
        }
        echo "}".PHP_EOL;
    }

    public function execute(string $package, int $depth): void{
        $packetManager = $this->packetManager;

        try {
            $dependencies = $packetManager->getDependencies($package, $depth);

            $graphvizArr = [];
            $this->fillDigraph($package, $dependencies, $graphvizArr);
            $this->showDigraph($graphvizArr);
        } catch (\Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }

}

$usage = "Usage: php Loader.php -name <package> [-depth <number>]" . PHP_EOL;
if($argc == 1) {
    echo "No arguments given" . PHP_EOL;
} elseif($argc == 2) {
    echo "No package given" . PHP_EOL;
    echo $usage;
} elseif($argv[1] != "-name") {
    echo "Invalid arguments given".PHP_EOL;
    echo $usage;
} elseif($argc >= 3 and $argv[1] == "-name") {
    $name = $argv[2];
    $depth = 0;
    if($argc === 4 and $argv[3] == "-depth") {
        echo "Invalid arguments given". PHP_EOL;
        return;
    }
    if($argc === 5 and $argv[3] == "-depth") {
        if(is_numeric($argv[4])) {
            $depth = $argv[4];
        } else {
            echo "Invalid arguments given". PHP_EOL;
            return;
        }
    }
    (new Loader())->execute($name, $depth);
} else {
    echo "Invalid arguments given".PHP_EOL;
    echo $usage;
}
