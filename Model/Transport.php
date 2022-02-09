<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model;

use SalesAndOrders\FeedTool\Model\Logger;

/**
 * Comment is required here
 */
class Transport
{

    protected $logger;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param  $endpointUrl
     * @param  array $postData
     * @param  bool $checkUrl
     * @return array
     */

    public function sendData($endpointUrl, $postData = [], $checkUrl = true)
    {
        $logger = $this->logger->create('endPoint_sendData_curl', 'sendData');
        //if ($checkUrl) {
        //    $endpointUrl = preg_replace("(^https?://)", "", $endpointUrl);
        //}
        // phpcs:disable
        ob_start();
        $out = fopen('php://output', 'w');
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
            CURLOPT_STDERR => $out,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Cache-Control: no-cache"
            ],
        ];
//        if ($checkUrl) {
//            $curlOption[CURLOPT_PORT] = 80;
//        }

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
        fclose($out);
        $debug = ob_get_clean();

        $logger->info('Response from  ' . $endpointUrl . ' URL');
        $logger->info($response);
        $logger->info('Error');
        $logger->info($err);
        if (empty($response) && empty($err)) {
            $err = $debug;
            $logger->err('ERROR');
            $logger->err($err); // write error to file log instead of printing
        } else {
            $logger->err('Info');
            $logger->info($info);
        }
        curl_close($curl);
        // phpcs:enable
        return ['response' => $response, 'err' => $err];
    }
}
