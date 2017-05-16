<?php

namespace Craft;

/**
 * Rollbar Plugin.
 *
 * Integrates Rollbar into Craft
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@itmundi.nl>
 * @copyright Copyright (c) 2015, Bob Olde Hampsink
 * @license   http://buildwithcraft.com/license Craft License Agreement
 *
 * @see      http://github.com/boboldehampsink
 * @since     1.0
 */
class RollbarPlugin extends BasePlugin
{
    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Rollbar');
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.5.0';
    }

    /**
     * Get plugin developer.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Bob Olde Hampsink';
    }

    /**
     * Get plugin developer url.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://github.com/boboldehampsink';
    }

    /**
     * Initialize Rollbar.
     */
    public function init()
    {
        // Require Rollbar vendor code
        require_once __DIR__.'/vendor/autoload.php';

        // Initialize Rollbar
        \Rollbar\Rollbar::init(array(
            'access_token' => craft()->config->get('accessToken', 'rollbar'),
            'environment' => CRAFT_ENVIRONMENT,
        ), false, false);

        // Log Craft Exceptions to Rollbar
        craft()->onException = function ($event) {
            // Short circuit - don't report 404s, or twig template {% exit 404 %} to Rollbar
            if ((($event->exception instanceof \CHttpException) && ($event->exception->statusCode == 404)) ||
                (($event->exception->getPrevious() instanceof \CHttpException) && ($event->exception->getPrevious()->statusCode == 404))) {
                return;
            }

            \Rollbar\Rollbar::report_exception($event->exception);
        };

        // Log Craft Errors to Rollbar
        craft()->onError = function ($event) {
            \Rollbar\Rollbar::report_message($event->message);
        };
    }
}
