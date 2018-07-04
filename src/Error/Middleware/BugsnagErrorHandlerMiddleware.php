<?php
/**
 * Created by PhpStorm.
 * User: Jefferson Simão Gonçalves
 * Email: gerson.simao.92@gmail.com
 * Date: 04/07/2018
 * Time: 18:38
 */

namespace Bugsnag\Error\Middleware;

use Bugsnag\Client;
use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Exception;
use Throwable;

class BugsnagErrorHandlerMiddleware extends ErrorHandlerMiddleware
{

    /**
     * Client instance.
     *
     * @var \Bugsnag\Client
     */
    protected $_client = null;

    public function __construct($exceptionRenderer = null, array $config = [])
    {
        parent::__construct($exceptionRenderer, $config);

        $apiKey = Configure::read('Bugsnag.apiKey');
        if (!$apiKey && env('BUGSNAG_API_KEY')) {
            $apiKey = env('BUGSNAG_API_KEY');
        }
        if ($apiKey) {
            $this->setClient(Client::make($apiKey));
        }
    }

    /**
     * Set client instance.
     *
     * @param \Bugsnag\Client $client
     *
     * @return void
     */
    public function setClient(Client $client)
    {
        $this->_client = $client;
    }

    public function __invoke($request, $response, $next)
    {
        if (!isset($this->_client)) {
            return parent::__invoke($request, $response, $next);
        }

        try {
            return $next($request, $response);
        } catch (Throwable $exception) {
            $this->_client->notifyException($exception);

            return $this->handleException($exception, $request, $response);
        } catch (Exception $exception) {
            $this->_client->notifyException($exception);

            return $this->handleException($exception, $request, $response);
        }
    }
}