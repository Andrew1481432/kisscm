<?php

/*
ДЗ №1. Эмулятор командной строки
Разработать эмулятор командной строки vshell. В качестве аргумента vshell принимает образ файловой системы известного формата (tar, zip).

Обратите внимание: программа должна запускаться прямо из командной строки, а файл с виртуальной файловой системой не нужно распаковывать у пользователя.
В vshell должны поддерживаться команды pwd, ls, cd и cat. Ваша задача сделать работу vshell как можно более похожей на сеанс bash в Linux. Реализовать vshell
можно на Python или других ЯП, но кроссплатформенным образом.
*/

if(!function_exists("readline")) {
    function readline($prompt = null){
        if($prompt){
            echo $prompt;
        }
        $fp = fopen("php://stdin","r");
        $line = rtrim(fgets($fp, 1024));
        return $line;
    }
}

function println(string $str) {
    echo $str.PHP_EOL;
}

final Class Loader {

    /** @var string  */
    private static $curDir = "";
    /** @var ZipArchive */
    private static $zip = "";

    private function __destruct(){
        // NOOP
    }

    /**
     * @return string
     */
    private static function getCatalogs(bool $local = true): array{
        $zip = self::$zip;
        $curDir = &self::$curDir;

        $count = $zip->numFiles;
        $catalogs = [];
        for($i=0; $i < $count; $i++) {
            $arrData = $zip->statIndex($i);
            $dir = $arrData["name"];
            if(substr($dir, 0, 2) == "__" or substr($dir, 0, 1) == ".") {
                continue;
            }

            $dirArray = explode("/", $dir);
            array_shift($dirArray);
            if(count($dirArray) == 0) {
                continue;
            }
            if(isset($dirArray[1]) && $dirArray[1] == "") {
                unset($dirArray[1]);
            }

            $nameFile = implode("/", $dirArray);
            if($curDir != "") {
                $cutNameFile = substr($nameFile, 0, mb_strlen($curDir));
                //var_dump([$cutNameFile, $curDir]);
                if(str_replace("/", "", $curDir) != str_replace("/", "", $cutNameFile)) {
                    continue;
                }

                $nameFile = str_replace(substr($curDir, 1), "", $nameFile);
                $nameFile = rtrim(substr($nameFile, 1), "/");
                $dirArray = explode("/", $nameFile);
            } else {
                $nameFile = rtrim($nameFile, "/");
            }
            if($local) {
                if (count($dirArray) > 1) { // проверяем только текущий каталог
                    continue;
                }
            }
            if(substr($nameFile, 0, 1) == "." or substr($nameFile, 0, 1) == "") { //скипаем временные файлы
                continue;
            }
           if($arrData["size"] == 0) {
               $catalogs[] = $nameFile;
           }
        }
        return $catalogs;
    }

    private static function getData(): array{
        $zip = self::$zip;
        $curDir = &self::$curDir;

        $count = $zip->numFiles;
        $catalogs = [];
        for($i=0; $i < $count; $i++) {
            $arrData = $zip->statIndex($i);
            $dir = $arrData["name"];
            if(substr($dir, 0, 2) == "__" or substr($dir, 0, 1) == ".") {
                continue;
            }

            $dirArray = explode("/", $dir);
            array_shift($dirArray);
            if(count($dirArray) == 0) {
                continue;
            }
            if(isset($dirArray[1]) && $dirArray[1] == "") {
                unset($dirArray[1]);
            }

            $nameFile = implode("/", $dirArray);
            if($curDir != "") {
                $cutNameFile = substr($nameFile, 0, mb_strlen($curDir));
                //var_dump([$cutNameFile, $curDir]);
                if(str_replace("/", "", $curDir) != str_replace("/", "", $cutNameFile)) {
                    continue;
                }

                $nameFile = str_replace(substr($curDir, 1), "", $nameFile);
                $nameFile = rtrim(substr($nameFile, 1), "/");
                $dirArray = explode("/", $nameFile);
            } else {
                $nameFile = rtrim($nameFile, "/");
            }
            if (count($dirArray) > 1) { // проверяем только текущий каталог
                continue;
            }
            if(substr($nameFile, 0, 1) == "." or substr($nameFile, 0, 1) == "") { //скипаем временные файлы
                continue;
            }
            $color = $arrData["size"]==0 ? "\033[34m" : "\033[92m";
            $catalogs[] = $color.$nameFile;
        }
        return $catalogs;
    }

    public static function execute(string $dir) {
        self::$zip = $zip = new ZipArchive();
        $res = $zip->open($dir, ZipArchive::RDONLY|ZipArchive::CREATE);
        if(!$res) {
            echo 'Invalid zip file given' . PHP_EOL;
            return;
        } else {
            echo "Opening zip file: $dir".PHP_EOL;
        }
        $running = true;

        $curDir = "";
        self::$curDir = &$curDir;
        while($running) {
            $showCurDir = substr($curDir, 1);
            $input = readline("root@vShell:/$showCurDir# ");
            if($input == false) {
                echo "Error input! Try again..." . PHP_EOL;
                continue;
            }

            /** В vshell должны поддерживаться команды pwd, ls, cd и cat*/
            $arrInput = explode(" ", $input);
            switch($arrInput[0] ?? '') {
                case "exit":
                    break 2;

                case "ping":
                    echo "pong".PHP_EOL;
                    break;

                case "pwd":
                    if($curDir == "") {
                        println("/");
                    } else {
                        println("/" . $curDir);
                    }
                    break;

                case "cat":
                    if(!isset($arrInput[1])) {
                        println("Not found args file!");
                        break;
                    }
                    $file = $arrInput[1];

                    $lastDir = explode("/", $dir);
                    $nameArchive = explode(".", $lastDir[count($lastDir)-1])[0];
                    var_dump($zip->getFromName($nameArchive.$curDir.DIRECTORY_SEPARATOR.$file));
                    break;

                case "ls":
                    $catalogs = self::getData();
                    println(implode(" ", $catalogs));
                    print("\033[0m");
                    break;

                case "cd":
                    $dirs = self::getCatalogs(false);
                    if(!isset($arrInput[1])) {
                        println("Not found args directory!");
                        break;
                    }
                    $catalog = $arrInput[1];
                    if(in_array($catalog, $dirs, true)) {
                        $curDir .= "/".$catalog;
                        break;
                    }
                    if($catalog == "..") {
                        $arrDir = explode("/", $curDir);
                        if(count($arrDir)>0) {
                            unset($arrDir[count($arrDir)-1]);
                            $curDir = implode("/", $arrDir);
                        }
                        break;
                    }
                    println($catalog.": No such file or directory");
                    break;

                default:
                   echo $arrInput[0].": command not found".PHP_EOL;
            }
        }
    }


}

$usage = "Usage: php Loader.php -zip <zipfile>" . PHP_EOL;
if($argc == 1) {
    echo "No arguments given" . PHP_EOL;
} elseif($argc == 2) {
    echo "No zip file given" . PHP_EOL;
    echo $usage;
} elseif($argc > 3 and $argv[1] != "-zip") {
    echo "Invalid arguments given".PHP_EOL;
    echo $usage;
} elseif($argc === 3 and $argv[1] == "-zip") {
    $name = $argv[2];
    if(!file_exists($name)) {
        echo 'File not found!' . PHP_EOL;
        return;
    }
    Loader::execute($name);
} else {
    echo "Invalid arguments given".PHP_EOL;
    echo $usage;
}
