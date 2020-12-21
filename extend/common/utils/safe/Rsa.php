<?php
namespace common\utils\safe;

use think\facade\Log;

class Rsa
{
    private $MAX_ENCRYPT_BLOCK = 117;

    private $MAX_DECRYPT_BLOCK = 128;

    // 初始化，生成公钥和私钥对
    public function __construct($length = 1024)
    {
        if ($length == 1024) {
            $this->MAX_ENCRYPT_BLOCK = 117;
            $this->MAX_DECRYPT_BLOCK = 128;
        } elseif ($length == 2048) {
            $this->MAX_ENCRYPT_BLOCK = 245;
            $this->MAX_DECRYPT_BLOCK = 256;
        } elseif ($length == 4096) {
            $this->MAX_ENCRYPT_BLOCK = 501;
            $this->MAX_DECRYPT_BLOCK = 512;
        } else {
            $this->MAX_ENCRYPT_BLOCK = 117;
            $this->MAX_DECRYPT_BLOCK = 128;
        }
    }

    /**
     * 将字符串格式公私钥格式化为pem格式公私钥
     * @param $secret_key
     * @param $type
     * @return string
     */
    public static function format_secret_key($secret_key, $type)
    {
        // 64个英文字符后接换行符"\n",最后再接换行符"\n"
        $key = (wordwrap($secret_key, 64, "\n", true)) . "\n";
        // 添加pem格式头和尾
        if ($type == 'pub') {
            $pem_key = "-----BEGIN PUBLIC KEY-----\n" . $key . "-----END PUBLIC KEY-----\n";
        } elseif ($type == 'pri') {
            $pem_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $key . "-----END RSA PRIVATE KEY-----\n";
        } else {
            Log::error("公私钥类型非法");
            $pem_key = "";
        }
        return $pem_key;
    }


    // 公钥加密数据
    public function publicEncrypt($data, $publicKey)
    {
        Log::info("公钥加密原文：" . $data);
        $publicKey = self::format_secret_key($publicKey, 'pub');
        $result  = '';
        $split = str_split($data, $this->MAX_ENCRYPT_BLOCK);
        foreach ($split as $part) {
            $encrypt = '';
            openssl_public_encrypt($part, $encrypt, $publicKey);
            $result .= $encrypt;
        }
        $result = base64_encode($result);
        Log::info("公钥加密结果：" . $result);
        return $result;
    }

    // 公钥解密数据
    public function publicDecrypt($data, $publicKey)
    {
        Log::info("公钥解密原文：" . $data);
        $publicKey = self::format_secret_key($publicKey, 'pub');
        $data = base64_decode($data);
        $result  = '';
        $split = str_split($data, $this->MAX_DECRYPT_BLOCK);
        foreach ($split as $part) {
            $decrypt = '';
            openssl_public_decrypt($part, $decrypt, $publicKey);
            $result .= $decrypt;
        }
        Log::info("公钥解密结果：" . $result);
        return $result;
    }

    // 私钥加密数据
    public function privateEncrypt($data, $privateKey)
    {
        Log::info("私钥加密原文：" . $data);
        $privateKey = self::format_secret_key($privateKey, 'pri');
        $result  = '';
        $split = str_split($data, $this->MAX_ENCRYPT_BLOCK);
        foreach ($split as $part) {
            $encrypt = '';
            openssl_private_encrypt($part, $encrypt, $privateKey);
            $result .= $encrypt;
        }
        $result = base64_encode($result);
        Log::info("私钥加密结果：" . $result);
        return $result;
    }

    // 私钥解密数据
    public function privateDecrypt($data, $privateKey)
    {
        Log::info("私钥解密原文：" . $data);
        $privateKey = self::format_secret_key($privateKey, 'pri');
        $data = base64_decode($data);
        $result  = '';
        $split = str_split($data, $this->MAX_DECRYPT_BLOCK);
        foreach ($split as $part) {
            $decrypt = '';
            openssl_private_decrypt($part, $decrypt, $privateKey);
            $result .= $decrypt;
        }
        Log::info("私钥解密结果：" . $result);
        return $result;
    }

    /**
     * 私钥解密(不切块)
     * @param string $encrypted
     * @return null
     */
    public function privateDecryptNotBlock($encrypted = '', $privateKey)
    {
        $privateKey = self::format_secret_key($privateKey, 'pri');
        if (!is_string($encrypted)) {
            return null;
        }
        $flag = openssl_private_decrypt(base64_decode($encrypted), $decrypted, $privateKey);
        return $flag ? $decrypted : null;
    }
}