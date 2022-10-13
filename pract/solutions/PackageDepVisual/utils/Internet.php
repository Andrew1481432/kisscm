<?php

declare(strict_types=1);

namespace utils;

class Internet {

    private function __construct() {
        // NOOP
    }

    /**
     * POSTs data to an URL
     * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
     *
     * @param string[]|string $args
     * @param string[]        $extraHeaders
     * @phpstan-param string|array<string, string> $args
     * @phpstan-param list<string>                 $extraHeaders
     */
    public static function postURL(string $page, $args, int $timeout = 20, array $extraHeaders = []) : ?InternetRequestResult{
        return self::simpleCurl($page, $timeout, $extraHeaders, [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $args
        ]);
    }

    /**
     * GETs an URL using cURL
     * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
     *
     * @param int         $timeout default 10
     * @param string[]    $extraHeaders
     * @phpstan-param list<string>          $extraHeaders
     */
    public static function getURL(string $page, int $timeout = 20, array $extraHeaders = []) : ?InternetRequestResult{
        return self::simpleCurl($page, $timeout, $extraHeaders);
    }

    /**
     * General cURL shorthand function.
     * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
     *
     *
     * @throws InternetException if a cURL error occurs
     */
    public static function simpleCurl(string $page, $timeout = 20, array $extraHeaders = [], array $extraOpts = []): InternetRequestResult{
        $ch = curl_init($page);
        if($ch === false){
            throw new InternetException("Unable to create new cURL session");
        }

        curl_setopt_array($ch, $extraOpts + [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FORBID_REUSE => 1,
                CURLOPT_FRESH_CONNECT => 1,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT_MS => (int) ($timeout * 1000),
                CURLOPT_TIMEOUT_MS => (int) ($timeout * 1000),
                CURLOPT_HTTPHEADER => array_merge(["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 PackageDepVisual 1.0.0"], $extraHeaders),
                CURLOPT_HEADER => true
            ]);
        try{
            $raw = curl_exec($ch);
            if($raw === false){
                throw new InternetException(curl_error($ch));
            }
            if(!is_string($raw)) throw new InternetException("curl_exec() should return string|false when CURLOPT_RETURNTRANSFER is set");
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $rawHeaders = substr($raw, 0, $headerSize);
            $body = substr($raw, $headerSize);
            $headers = [];
            foreach(explode("\r\n\r\n", $rawHeaders) as $rawHeaderGroup){
                $headerGroup = [];
                foreach(explode("\r\n", $rawHeaderGroup) as $line){
                    $nameValue = explode(":", $line, 2);
                    if(isset($nameValue[1])){
                        $headerGroup[trim(strtolower($nameValue[0]))] = trim($nameValue[1]);
                    }
                }
                $headers[] = $headerGroup;
            }
            return new InternetRequestResult($headers, $body, $httpCode);
        }finally{
            curl_close($ch);
        }
    }


}