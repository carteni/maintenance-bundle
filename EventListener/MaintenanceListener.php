<?php

/*
 * This file is part of the MesMaintenanceBundle package.
 *
 * (c) Francesco Cartenì <http://www.multimediaexperiencestudio.it/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mes\Misc\MaintenanceBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class MaintenanceListener.
 */
class MaintenanceListener
{
    private $controller;

    /** @var RequestMatcherInterface */
    private $matcher;

    /** @var bool */
    private $debug;

    /** @var array */
    private $ips;

    /** @var bool */
    private $allowed = false;

    /** @var bool */
    private $profiler_allowed = false;

    /** @var int */
    private $statusCode = Response::HTTP_OK;

    /**
     * MaintenanceListener constructor.
     *
     * @param callable                $controller
     * @param RequestMatcherInterface $matcher
     * @param array                   $ips
     */
    public function __construct(callable $controller, RequestMatcherInterface $matcher, $debug, array $ips = array())
    {
        $this->controller = $controller;
        $this->matcher = $matcher;
        $this->debug = $debug;
        $this->ips = $ips;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (($this->profiler_allowed = ($this->debug && preg_match('/(_wdt|_profiler|_error)/', $request->getPathInfo())))) {
            return;
        }

        if ($this->allowed = (0 < count($this->ips) && $this->matcher->matches($request))) {
            return;
        }

        $this->statusCode = Response::HTTP_SERVICE_UNAVAILABLE;

        $event->setController($this->controller);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $event->getResponse()
              ->setStatusCode($this->statusCode);
    }
}
