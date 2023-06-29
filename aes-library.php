<?php

function encrypt($data, $key)
{
    $cipher = "aes-256-cbc";
    $options = OPENSSL_RAW_DATA;
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, $cipher, $key, $options, $iv);
    $result = base64_encode($iv . $encrypted);
    return $result;
}

function decrypt($data, $key)
{
    $cipher = "aes-256-cbc";
    $options = OPENSSL_RAW_DATA;
    $iv_length = openssl_cipher_iv_length($cipher);
    $decoded = base64_decode($data);
    $iv = substr($decoded, 0, $iv_length);
    $encrypted_payload = substr($decoded, $iv_length);
    $result = openssl_decrypt($encrypted_payload, $cipher, $key, $options, $iv);
    return $result;
}

?>
