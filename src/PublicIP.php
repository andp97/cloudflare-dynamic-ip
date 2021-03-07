<?php
namespace App;

use GuzzleHttp;

class PublicIP
{
    public static function get(): string{
        try {
            $client = new GuzzleHttp\Client(['base_uri' => 'https://ip.andreapavone.com','timeout' => 1]);
            $res = $client->request('GET', '/api/v2');
            return json_decode($res->getBody(), true)['data']['ip'] ?? '';
        }catch (\Exception | GuzzleHttp\Exception\TransferException | GuzzleHttp\Exception\ClientException | GuzzleHttp\Exception\RequestException $ex){
            return '';
        }

    }
}