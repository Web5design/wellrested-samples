<?php

namespace ApiSample;

use pjdietz\WellRESTed\Route;
use pjdietz\WellRESTed\Router;

/**
 * Loads and instantiates handlers based on URI.
 */
class ApiSampleRouter extends Router
{
    // Customize as needed based on your server.
    const HANDLER_NAMESPACE = '\\ApiSample\\Handlers\\';
    const URI_PREFIX = '/api';

    public function __construct()
    {
        parent::__construct();
        $this->addTemplate('/articles/', 'ArticleCollectionHandler');
        $this->addTemplate('/articles/{id}', 'ArticleItemHandler', array('id' => Route::RE_NUM));
        $this->addTemplate(
            '/articles/{slug}',
            'ArticleItemHandler',
            array('slug' => Route::RE_SLUG)
        );
    }

    public function addTemplate($template, $handlerClassName, $variables = null)
    {
        $template = self::URI_PREFIX . $template;
        $handlerClassName = self::HANDLER_NAMESPACE . $handlerClassName;

        $this->addRoute(
            Route::newFromUriTemplate(
                $template,
                $handlerClassName,
                $variables
            )
        );
    }

}
