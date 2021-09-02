<?php
namespace common\models;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class Decrypt
{

    public static function bossGetTokenInfo()
    {
        $jwt = self::getToken();
        if (!$jwt) {
            return false;
        }
        $jwtData = explode('.', $jwt);
        // 验证 boss端token
        $result = self::checkBossToken($jwt);

        if (!$result) {
            return false;
        }
        return json_decode(base64_decode($jwtData[1]));
    }

    public static function getToken()
    {
        $header = \Yii::$app->request->headers->toArray();
        $jwt = $header['authorization'][0] ?? null;
        $jwtData = explode('.', $jwt);
        if (count($jwtData) != 3) {
            return false;
        }
        return $jwt;
    }

    /**
     * @param string $token
     * @return bool
     */
    public static function checkBossToken($token = '')
    {
        if (!$token) {
            $header = \Yii::$app->request->headers->toArray();
            $token = isset($header['authorization'][0]) ? $header['authorization'][0] : null;
        }
        $token = (new Parser())->parse(strval($token));
        // 检测注册时间
        $iat = $token->getClaim('iat');
        if (((3600*24*30) + $iat) < time()) {
            return false;
        }
        // 检测签名
        return $token->verify(self::getSigner(), self::getSignKey());
    }

    /**
     * 生成token
     * @param $adminId
     * @return bool|\Lcobucci\JWT\Token
     */
    public static function createBossToken($adminId)
    {
        $user = Users::find()->where(['id' => $adminId])->limit(1)->one();
        if (!$user) {
            return false;
        }
        // sub信息
        $userStr = 'user_' . $user->phone . '_' . $user->id;

        $token = (new Builder())
            ->withClaim('id',"$adminId")
            ->setSubject($userStr)
            ->setIssuedAt(time())
            ->sign(self::getSigner(), self::getSignKey())
            ->getToken();
        return strval($token);
    }

    /**
     * 签名方式
     * @return Sha256
     */
    private static function getSigner()
    {
        return new Sha256();
    }

    /**
     * 签名密钥
     * @return string
     */
    private static function getSignKey()
    {
        $key = \Yii::$app->params['jwt-secret'] ?? null;
        if (!$key) { // 如果key不存在,写入local配置
            $key = 'jwt-e' . substr(microtime(), 2, 6) . uniqid();
            // 检测是否是boss模块中
            $module = \Yii::$app->id;
            if ($module != 'app-backend') {
                return $key;
            }
            $filename = \Yii::getAlias('@backend/config/params-local.php');
            if(!is_writeable($filename)) {
                return 'file can not writable!';
            }
            $content = '<?php return [' . "'jwt-secret' =>'{$key}'" . '];';
            file_put_contents($filename, $content . "\n");
        }
        return $key;
    }

}