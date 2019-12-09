<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\wechat\service;

use think\admin\Service;

/**
 * Class WechatService
 * @package app\wechat\serivce
 *
 * @method \WeChat\Card WeChatCard() static 微信卡券管理
 * @method \WeChat\Custom WeChatCustom() static 微信客服消息
 * @method \WeChat\Limit WeChatLimit() static 接口调用频次限制
 * @method \WeChat\Media WeChatMedia() static 微信素材管理
 * @method \WeChat\Menu WeChatMenu() static 微信菜单管理
 * @method \WeChat\Oauth WeChatOauth() static 微信网页授权
 * @method \WeChat\Pay WeChatPay() static 微信支付商户
 * @method \WeChat\Product WeChatProduct() static 微信商店管理
 * @method \WeChat\Qrcode WeChatQrcode() static 微信二维码管理
 * @method \WeChat\Receive WeChatReceive() static 微信推送管理
 * @method \WeChat\Scan WeChatScan() static 微信扫一扫接入管理
 * @method \WeChat\Script WeChatScript() static 微信前端支持
 * @method \WeChat\Shake WeChatShake() static 微信揺一揺周边
 * @method \WeChat\Tags WeChatTags() static 微信用户标签管理
 * @method \WeChat\Template WeChatTemplate() static 微信模板消息
 * @method \WeChat\User WeChatUser() static 微信粉丝管理
 * @method \WeChat\Wifi WeChatWifi() static 微信门店WIFI管理
 *
 * ----- WeMini -----
 * @method \WeMini\Account WeMiniAccount() static 小程序账号管理
 * @method \WeMini\Basic WeMiniBasic() static 小程序基础信息设置
 * @method \WeMini\Code WeMiniCode() static 小程序代码管理
 * @method \WeMini\Domain WeMiniDomain() static 小程序域名管理
 * @method \WeMini\Tester WeMinitester() static 小程序成员管理
 * @method \WeMini\User WeMiniUser() static 小程序帐号管理
 * --------------------
 * @method \WeMini\Crypt WeMiniCrypt() static 小程序数据加密处理
 * @method \WeMini\Delivery WeMiniDelivery() static 小程序即时配送
 * @method \WeMini\Image WeMiniImage() static 小程序图像处理
 * @method \WeMini\Logistics WeMiniLogistics() static 小程序物流助手
 * @method \WeMini\Message WeMiniMessage() static 小程序动态消息
 * @method \WeMini\Ocr WeMiniOcr() static 小程序ORC服务
 * @method \WeMini\Plugs WeMiniPlugs() static 小程序插件管理
 * @method \WeMini\Poi WeMiniPoi() static 小程序地址管理
 * @method \WeMini\Qrcode WeMiniQrcode() static 小程序二维码管理
 * @method \WeMini\Security WeMiniSecurity() static 小程序内容安全
 * @method \WeMini\Soter WeMiniSoter() static 小程序生物认证
 * @method \WeMini\Template WeMiniTemplate() static 小程序模板消息支持
 * @method \WeMini\Total WeMiniTotal() static 小程序数据接口
 *
 * ----- WePay -----
 * @method \WePay\Bill WePayBill() static 微信商户账单及评论
 * @method \WePay\Order WePayOrder() static 微信商户订单
 * @method \WePay\Refund WePayRefund() static 微信商户退款
 * @method \WePay\Coupon WePayCoupon() static 微信商户代金券
 * @method \WePay\Redpack WePayRedpack() static 微信红包支持
 * @method \WePay\Transfers WePayTransfers() static 微信商户打款到零钱
 * @method \WePay\TransfersBank WePayTransfersBank() static 微信商户打款到银行卡
 *
 * ----- WeOpen -----
 * @method \WeOpen\Login WeOpenLogin() static 第三方微信登录
 * @method \WeOpen\Service WeOpenService() static 第三方服务
 *
 * ----- ThinkService -----
 * @method mixed ThinkAdminConfig($appid) static 平台服务配置
 */
class WechatService extends Service
{

    /**
     * 静态初始化对象
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \SoapFault
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function __callStatic($name, $arguments)
    {
        if (sysconf('wechat.type') === 'api') {
            list($type, $class) = ['-', '-'];
            foreach (['WeChat', 'WeMini', 'WeOpen', 'WePay', 'ThinkAdmin'] as $type) {
                if (strpos($name, $type) === 0) {
                    list(, $class) = explode($type, $name);
                    break;
                }
            }
            if ("{$type}{$class}" !== $name) {
                throw new \think\Exception("class {$name} not defined.");
            }
            $classname = "\\{$type}\\{$class}";
            if ($type === 'ThinkAdmin') {
                throw new \think\Exception("Interface mode cannot instance {$classname}");
            }
            return new $classname(self::instance()->getConfig());
        } else {
            list($appid, $appkey) = [sysconf('wechat.thr_appid'), sysconf('wechat.thr_appkey')];
            $data = ['class' => $name, 'appid' => $appid, 'time' => time(), 'nostr' => uniqid()];
            $data['sign'] = md5("{$data['class']}#{$appid}#{$appkey}#{$data['time']}#{$data['nostr']}");
            $token = enbase64url(json_encode($data, JSON_UNESCAPED_UNICODE));
            if (class_exists('Yar_Client')) {
                $url = "http://open.cuci.cc/service/api.client/yar?not_init_session=1&token={$token}";
                $client = new \Yar_Client($url);
            } else {
                $url = "http://open.cuci.cc/service/api.client/soap?not_init_session=1&token={$token}";
                $client = new \SoapClient(null, ['location' => $url, 'uri' => "thinkadmin"]);
            }
            try {
                $exception = new \think\Exception($client->getMessage(), $client->getCode());
            } catch (\Exception $exception) {
                $exception = null;
            }
            if ($exception instanceof \Exception) {
                throw $exception;
            }
            return $client;
        }
    }

    /**
     * 获取当前微信APPID
     * @return bool|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAppid()
    {
        if ($this->getType() === 'api') {
            return sysconf('wechat.appid');
        } else {
            return sysconf('wechat.thr_appid');
        }
    }

    /**
     * 获取接口授权模式
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getType()
    {
        $type = strtolower(sysconf('wechat.type'));
        if (in_array($type, ['api', 'thr'])) return $type;
        throw new \think\Exception('请在后台配置微信对接授权模式');
    }

    /**
     * 获取公众号配置参数
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfig()
    {
        return [
            'token'          => sysconf('wechat.token'),
            'appid'          => sysconf('wechat.appid'),
            'appsecret'      => sysconf('wechat.appsecret'),
            'encodingaeskey' => sysconf('wechat.encodingaeskey'),
            'cache_path'     => $this->app->getRuntimePath() . 'wechat',
        ];
    }

    /**
     * 获取网页授权信息
     * @param string $url
     * @param integer $isfull
     * @param boolean $isRedirect
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getWebOauthInfo($url, $isfull = 0, $isRedirect = true)
    {
        $appid = $this->getAppid();
        list($openid, $fansinfo) = [$this->app->session->get("{$appid}_openid"), $this->app->session->get("{$appid}_fansinfo")];
        if ((empty($isfull) && !empty($openid)) || (!empty($isfull) && !empty($openid) && !empty($fansinfo))) {
            empty($fansinfo) || FansService::instance()->set($fansinfo);
            return ['openid' => $openid, 'fansinfo' => $fansinfo];
        }
        if ($this->getType() === 'api') {
            $wechat = self::WeChatOauth();
            if (input('state') !== $appid) {
                $snsapi = empty($isfull) ? 'snsapi_base' : 'snsapi_userinfo';
                $param = (strpos($url, '?') !== false ? '&' : '?') . 'rcode=' . encode($url);
                $OauthUrl = $wechat->getOauthRedirect($url . $param, $appid, $snsapi);
                if ($isRedirect) redirect($OauthUrl, 301)->send();
                exit("window.location.href='{$OauthUrl}'");
            }
            if (($token = $wechat->getOauthAccessToken()) && isset($token['openid'])) {
                $this->app->session->set("{$appid}_openid", $openid = $token['openid']);
                if (empty($isfull) && input('rcode')) {
                    redirect(enbase64url(input('rcode')), 301)->send();
                }
                $this->app->session->set("{$appid}_fansinfo", $fansinfo = $wechat->getUserInfo($token['access_token'], $openid));
                empty($fansinfo) || FansService::instance()->set($fansinfo);
            }
            redirect(enbase64url(input('rcode')), 301)->send();
        } else {
            $result = self::ThinkAdminConfig()->oauth(session_id(), $url, $isfull);
            session("{$appid}_openid", $openid = $result['openid']);
            session("{$appid}_fansinfo", $fansinfo = $result['fans']);
            if ((empty($isfull) && !empty($openid)) || (!empty($isfull) && !empty($openid) && !empty($fansinfo))) {
                empty($fansinfo) || FansService::instance()->set($fansinfo);
                return ['openid' => $openid, 'fansinfo' => $fansinfo];
            }
            if ($isRedirect && !empty($result['url'])) {
                redirect($result['url'], 301)->send();
            }
            exit("window.location.href='{$result['url']}'");
        }
    }

    /**
     * 获取微信网页JSSDK
     * @param string $url JS签名地址
     * @return array
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getWebJssdkSign($url = null)
    {
        $url = is_null($url) ? $this->app->request->url(true) : $url;
        if ($this->getType() === 'api') {
            return self::WeChatScript()->getJsSign($url);
        } else {
            return self::ThinkAdminConfig($this->getAppid())->jsSign($url);
        }
    }
}