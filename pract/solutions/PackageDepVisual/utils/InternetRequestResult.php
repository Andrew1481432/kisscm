<?php

namespace utils;

final class InternetRequestResult{

    private array $headers;
    private string $body;
    private int $code;

    public function __construct(array $headers, string $body, int $code){
        $this->headers = $headers;
        $this->body = $body;
        $this->code = $code;
    }

    /**
     * @return string[][]
     * @phpstan-return list<array<string, string>>
     */
    public function getHeaders() : array{ return $this->headers; }

    public function getBody() : string{ return $this->body; }

    public function getCode() : int{ return $this->code; }
}