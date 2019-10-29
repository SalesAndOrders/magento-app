<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model;

/**
 * Comment is required here
 */
class Transport
{
    /**
     * @param  $endpointUrl
     * @param  array $postData
     * @param  bool $checkUrl
     * @return array
     */
    public function sendData($endpointUrl, $postData = [], $checkUrl = true)
    {
        //if ($checkUrl) {
        //    $endpointUrl = preg_replace("(^https?://)", "", $endpointUrl);
        //}
        //ob_start();
        //$out = fopen('php://output', 'w');
        $curl = curl_init();
        $curlOptions = [
            CURLOPT_URL => $endpointUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_VERBOSE => true,
            //CURLOPT_STDERR => $out,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Cache-Control: no-cache"
            ],
        ];
        //if ($checkUrl) {
        //    $curlOption[CURLOPT_PORT] = 80;
        //}

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
        //fclose($out);
        //$debug = ob_get_clean();
        //if (empty($response) && empty($err)) {
        //    $err = $debug;
        //}
        curl_close($curl);
        return ['response' => $response, 'err' => $err];
    }
}
