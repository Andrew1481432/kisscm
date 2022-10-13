<?php

namespace manager;

use Loader;
use utils\Internet;

class NpmManager extends PacketManager {

    public function name(): string {
        return "npm";
    }

    private function getUrl() {
        return "https://registry.npmjs.org/";
    }

    /**
     * @throws PacketManagerException
     * @throws \JsonException
     */
    public function getDependencies(string $name, int $depth = 0): array{
        if(Loader::DEBUG_MODE) {
            echo "Получение пакета: " . $name . PHP_EOL;
        }
        $data = json_decode(Internet::getURL($this->getUrl().$name)->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if(Loader::DEBUG_MODE) {
            echo "Пакет получен!" . PHP_EOL;
        }

        if(isset($data['error'])) {
            throw new PacketManagerException($data['error']);
        }
        if(!isset($data["dist-tags"]["latest"])) {
            throw new PacketManagerException("[dist-tags][latest] not found field!");
        }
        $latestVersion = $data["dist-tags"]["latest"];
        $actualPackage = $data["versions"][$latestVersion];
        if(!isset($actualPackage["dependencies"])) {
            throw new PacketManagerException("`dependencies` not found field for package ".$name."!");
        }
        $dependencies = $actualPackage["dependencies"];
        foreach($dependencies as $key => &$val) {
            if($depth == 0) {
                $val = [];
            } else {
                try {
                    $val = $this->getDependencies($key, $depth - 1);
                } catch (\Exception $e) {
                    $val = [];
                }
            }
        }
        return $dependencies;
    }

}