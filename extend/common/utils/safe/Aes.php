<?php
namespace common\utils\safe;

use think\facade\Log;

class Aes
{

    /**
     * 秘钥
     * @var string
     */
    private $encryptKey = "";

    public function __construct($encryptKey)
    {
        $this->encryptKey = $encryptKey ?? "";
    }

    /**
     * 加密
     * @param $encryptStr
     * @return false|string
     */
    public function encrypt($encryptStr)
    {
        Log::info("aes加密原文： {$encryptStr}");
        $encryptKey = $this->encryptKey;
        $encrypt = openssl_encrypt($encryptStr, "AES-128-ECB", $encryptKey, OPENSSL_RAW_DATA);
        $base64Encode = base64_encode($encrypt);
        Log::info("aes加密结果: {$base64Encode}");
        return $base64Encode;
    }

    /**
     * 解密
     * @param $encryptStr
     * @return false|string
     */
    public function decrypt($encryptStr)
    {
        Log::info("aes解密原文： {$encryptStr}");
        $encryptKey = $this->encryptKey;
        $base64 = base64_decode($encryptStr);
        $decrypt =  openssl_decrypt($base64, 'AES-128-ECB', $encryptKey, OPENSSL_RAW_DATA);
        Log::info("aes解密字符串: {$decrypt}");
        return $decrypt;
    }
}