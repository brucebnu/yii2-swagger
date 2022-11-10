<?php

namespace brucebnu\swagger;

use Yii;
use yii\base\Action;
use yii\caching\Cache;
use yii\di\Instance;
use yii\web\Response;
use OpenApi\Generator;
use OpenApi\Annotations\OpenApi;

/**
 * Class OpenAPIRenderer is responsible for generating the JSON spec.
 *
 * @package yii2mod\swagger\actions
 * https://www.shuzhiduo.com/A/MyJx1YQE5n/
 * https://swagger.io/docs/specification/authentication/
 * https://zircote.github.io/swagger-php/reference/processors.html
 *
 */
class OpenAPIRenderer extends Action
{
    /**
     * @var string|array|\Symfony\Component\Finder\Finder The directory(s) or filename(s).
     * If you configured the directory must be full path of the directory.
     */
    public $scanDir;

    /**
     * @var array the options passed to `Swagger`, Please refer the `Swagger\scan` function for more information
     */
    public $scanOptions = [
        //解释权别名
        'aliases' => [
            'oa'=>'OpenApi\\Annotations',
            'swg'=>'OpenApi\\Annotations'
        ]
    ];

    /**
     * @var Cache|array|string the cache used to improve generating api documentation performance. This can be one of the followings:
     *
     * - an application component ID (e.g. `cache`)
     * - a configuration array
     * - a [[yii\caching\Cache]] object
     *
     * When this is not set, it means caching is not enabled
     */
    public $cache = 'cache';

     public  $enableCache = true;
    /**
     * @var int default duration in seconds before the cache will expire
     */
    public $cacheDuration = 360;

    /**
     * @var string the key used to store swagger data in cache
     */
    public $cacheKey = 'api-swagger-cache';

    public $info;


    //二维数组 请求url
    public $servers ;

    //认证与授权
    //https://openid.net/specs/openid-connect-discovery-1_0.html#JWK
    public $components = [
        //type：授权协议，枚举值有：apiKey、http、oauth2、openIdConnect
        //description：安全方法的描述，尽可能的详细，包含使用示例
        //name：安全密钥 apiKey 在 HTTP Header 请求中的名字
        //in：安全密钥 apiKey 在 HTTP 传输中的位置，枚举值有：query，header，cookie
        'securitySchemes'=>[
            //            'ApiKeyAuth'=>[
            //                'type'=>'apiKey',
            //                'in'=>'header',
            //                'name'=> 'X-API-Key'
            //            ],
            //https://openid.net/specs/openid-connect-discovery-1_0.html
//                        'openId'=>[
//                            'type'=>'openIdConnect',
//                            'description'=>'jET 配置',
//                            'openIdConnectUrl'=> 'http://api.tuishui.cn/site/jwt-config',
//                        ],
                        'bearerAuth'=>[
                            'type'=>'http',
                            'scheme'=>'bearer',
                            'bearerFormat'=> 'JWT',
                        ],
        ],
    ];


    /**
     * @var array[] components:
    securitySchemes:
    openId:
    type: openIdConnect
    openIdConnectUrl: /.well-known/openid-configuration
     */
    public $security = [
//        ['openId'=>[]]
        ['bearerAuth'=>[]]
    ];
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->enableCORS();
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::class);
        }
    }

    /**
     * @inheritdoc
     */
    public function run(): Response
    {
        $this->clearCache();

        if ($this->enableCache) {
            if (($openAPi = $this->cache->get($this->cacheKey)) === false) {
                $openAPi = $this->getOpenApi();
                $this->cache->set($this->cacheKey, $openAPi);
            }
        } else {
            $openAPi = $this->getOpenApi();
        }

        $openAPi->servers   = $this->servers ;
        $openAPi->info    = $this->info;
        $openAPi->components= $this->components;
        $openAPi->security= $this->security;
        return $this->controller->asJson($openAPi);
    }



    /**
     * 是否清除缓存
     * @throws ExitException
     */
    protected function clearCache()
    {
        $clearCache = Yii::$app->getRequest()->get('clear-cache', false);
        if ($clearCache !== false) {
            $this->cache->delete($this->cacheKey);
            Yii::$app->response->content = 'Succeed clear swagger api cache.';
            Yii::$app->end();
        }
    }

    /**
     * Scan the filesystem for swagger annotations and build swagger-documentation.
     *
     * @return Swagger
     */
    protected function getOpenApi() :OpenApi
    {

        $openapi = \OpenApi\Generator::scan($this->scanDir,$this->scanOptions);

        return $openapi;
    }

    /**
     * Enable CORS
     */
    protected function enableCORS(): void
    {
        $req  = Yii::$app->getRequest()->getHeaders();
        $req->set('Access-Control-Allow-Headers', '*');
        $req->set('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT');
        $req->set('Access-Control-Allow-Origin', '*');

        $headers = Yii::$app->getResponse()->getHeaders();

        $headers->set('Access-Control-Allow-Headers', 'Content-Type, api_key, Authorization');
        $headers->set('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT');
        $headers->set('Access-Control-Allow-Origin', '*');
    }
}
