<?php

namespace core;

class HttpClient{


    public function request($postURL, $dataToPost, $curlTYPE, $headers)
    {
        $results = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postURL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, '0');
        if ($curlTYPE == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        }
        if ($curlTYPE == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        if ($curlTYPE == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($curlTYPE == 'GET') {
            curl_setopt($ch, CURLOPT_POST, 0);
        }
        if (is_array($dataToPost)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToPost));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToPost);
        }


        $results = curl_exec($ch);
        //$this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $results;
    }
}