<?php

namespace manager;

abstract class PacketManager {

    abstract public function name(): string;

    /**
     * @param string $name
     * @param int    $depth
     * @return array key(зависимость) -> [
     *                                      key(зав) -> [..
     *                                      key(зав) -> [..
     *                                  ]
     */
    abstract public function getDependencies(string $name, int $depth = 0): array;


}