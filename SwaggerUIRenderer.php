<?php

namespace brucebnu\swagger;

use Yii;
use yii\base\Action;

/**
 * Class SwaggerUIRenderer renders the UI (HTML/JS/CSS).
 *
 * @package yii2mod\swagger
 */
class SwaggerUIRenderer extends Action
{
    /**
     * @var string the rest url configuration
     */
    public $restUrl;

    /**
     * @var string base swagger template
     */
    public $view = '@base/brucebnu/yii2-swagger/views/index';

    /**
     * @var null|string|false the name of the layout to be applied to this controller's views.
     * This property mainly affects the behavior of [[render()]].
     * Defaults to null, meaning the actual layout value should inherit that from [[module]]'s layout value.
     * If false, no layout will be applied.
     */
    public $layout = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        //$this->controller->layout = $this->layout;
//dd($this->controller->layout, $this->layout);
        $this->layout = '@backend/themes/'.Yii::$app->params['currentTheme'].'/layout/master.php';
        //dd();
        return $this->controller->render($this->view, [
            'restUrl' => $this->restUrl,
        ]);
    }
}
