<?php
namespace common\utils\safe;

/**
 * RSA + AES 加解密工具类
 * Class RsaAesUtils
 * @package core\utils
 */
class RsaAesUtils {

    /**
     * 我们这一侧只用私钥加解密
     * @var string
     */
    private $rsaPrivateKey = "";

    /**
     * 开发者的公钥认证
     * @var string
     */
    private $developRsaPublicKey = "";

    /**
     * @var Rsa
     */
    private $rsa;

    /**
     * @var Aes
     */
    private $aes;
    /**
     * RsaAesUtils constructor.
     * @param $aesEncryptKey
     * @param $aesIv
     * @param $rsaPrivateKey
     */
    public function __construct($aesEncryptKey, $rsaPrivateKey, $developRsaPublicKey)
    {
        $this->rsaPrivateKey = $rsaPrivateKey ?? "";
        $this->developRsaPublicKey = $developRsaPublicKey ?? "";
        $this->rsa = new Rsa();
        $this->aes = new Aes($aesEncryptKey);
    }

    /**
     * 加密 先RSA私钥加密 再 AES
     * @param $encryptString
     * @return false|string
     */
    public function privateEncrypt($encryptString)
    {
        $encryptString = $this->aes->encrypt($encryptString);
        $rsaEncryptString = $this->rsa->privateEncrypt($encryptString, $this->rsaPrivateKey);
        if ($this->developRsaPublicKey) {
            $rsaEncryptString = $this->rsa->publicEncrypt($rsaEncryptString, $this->developRsaPublicKey);
        }
        return $rsaEncryptString;
    }

    /**
     * 解密
     * @param $decryptString
     * @return string
     */
    public function privateDecrypt($decryptString)
    {
        if ($this->developRsaPublicKey) {
            $decryptString = $rsaEncryptString = $this->rsa->publicDecrypt($decryptString, $this->developRsaPublicKey);
        }
        $decryptString = $this->rsa->privateDecrypt($decryptString, $this->rsaPrivateKey);
        $aesDecryptString = $this->aes->decrypt($decryptString);
        return $aesDecryptString;
    }

    /**
     * 公钥加密 测试使用
     * @param $encryptString
     * @param $publicKey
     * @return false|string
     */
    public function publicEncrypt($encryptString, $publicKey)
    {
        $encryptString = $this->aes->encrypt($encryptString);
        $rsaEncryptString = $this->rsa->publicEncrypt($encryptString, $publicKey);
        return $rsaEncryptString;
    }

    /**
     * RSA公钥解密 测试使用
     * @param $decryptString
     * @param $publicKey
     * @return string
     */
    public function publicDecrypt($decryptString, $publicKey)
    {
        $decryptString = $this->rsa->publicDecrypt($decryptString, $publicKey);
        $aesDecryptString = $this->aes->decrypt($decryptString);
        return $aesDecryptString;
    }

}