<?php

namespace SalesAndOrders\FeedTool\Model;

/**
 * Comment is required here
 */
class Transport
{
    /**
     * @param  $endpointUrl
     * @param  array       $postData
     * @return array
     */
    public function sendData($endpointUrl, $postData = [])
    {
        $curl = curl_init();

        $curlOptions = [
            CURLOPT_URL => $endpointUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "cache-control: no-cache"
            ],
        ];

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return ['response' => $response, 'err' => $err];
    }
}
