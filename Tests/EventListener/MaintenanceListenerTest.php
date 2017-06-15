<?php

/*
 * This file is part of the MesMaintenanceBundle package.
 *
 * (c) Francesco Cartenì <http://www.multimediaexperiencestudio.it/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mes\Misc\MaintenanceBundle\Tests\EventListener;

use Mes\Misc\MaintenanceBundle\EventListener\MaintenanceListener;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * Class MaintenanceListenerTest.
 */
class MaintenanceListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $response;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $controller;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $event;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $responseEvent;

    protected function setUp()
    {
        // Request
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                              ->getMock();

        // Response
        $this->response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
                               ->getMock();

        // Controller
        $this->controller = $this->getMockBuilder('Mes\Misc\MaintenanceBundle\Controller\MaintenanceController')
                                 ->disableOriginalConstructor()
                                 ->getMock();

        // Controller Event
        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
                            ->disableOriginalConstructor()
                            ->getMock();

        // Response Event
        $this->responseEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                                    ->disableOriginalConstructor()
                                    ->getMock();
    }

    protected function tearDown()
    {
        $this->request = null;
        $this->response = null;
        $this->controller = null;
        $this->event = null;
        $this->responseEvent = null;
    }

    /**
     * @dataProvider loadAllowedIpsProvider
     *
     * @param $ipsAllowed
     * @param $ip
     */
    public function testAllowedIpsInMaintenanceModeNoDebug($ipsAllowed, $ip)
    {
        // Request Matcher
        $matcher = $this->createRequestMatcher($ipsAllowed);

        $this->event->expects($this->once())
                    ->method('getRequest')
                    ->will($this->returnValue($this->request));

        // debug is false.
        $this->request->expects($this->never())
                      ->method('getPathInfo');

        // In RequestMatcher::matches.
        $this->request->expects($this->once())
                      ->method('getClientIp')
                      ->will($this->returnValue($ip));

        /** @var MaintenanceListener $listener */
        $listener = $this->getListener($matcher, false, $ipsAllowed);

        $listener->onKernelController($this->event);
    }

    /**
     * @dataProvider loadAllowedIpsProvider
     *
     * @param $ipsAllowed
     * @param $ip
     */
    public function testIpsAllowedMaintenanceModeDebug($ipsAllowed, $ip)
    {
        // Request Matcher
        $matcher = $this->createRequestMatcher($ipsAllowed);

        $this->event->expects($this->once())
                    ->method('getRequest')
                    ->will($this->returnValue($this->request));

        // debug is true.
        $this->request->expects($this->once())
                      ->method('getPathInfo')
                      ->will($this->returnValue('/_profiler/123456'));

        // In RequestMatcher::matches.
        $this->request->expects($this->never())
                      ->method('getClientIp');

        /** @var MaintenanceListener $listener */
        $listener = $this->getListener($matcher, true, $ipsAllowed);

        $listener->onKernelController($this->event);
    }

    /**
     * @dataProvider loadNotAllowedIpsProvider
     *
     * @param $ipsAllowed
     * @param $ip
     */
    public function testIpsNotAllowedMaintenanceModeNoDebug($ipsAllowed, $ip)
    {
        // Request Matcher
        $matcher = $this->createRequestMatcher($ipsAllowed);

        $this->event->expects($this->once())
                    ->method('getRequest')
                    ->will($this->returnValue($this->request));

        // debug is false.
        $this->request->expects($this->never())
                      ->method('getPathInfo');

        // In RequestMatcher::matches.
        $this->request->expects($this->once())
                      ->method('getClientIp')
                      ->willReturn($ip);

        $this->event->expects($this->once())
                    ->method('setController');

        /** @var MaintenanceListener $listener */
        $listener = $this->getListener($matcher, false, $ipsAllowed);

        $listener->onKernelController($this->event);
    }

    public function testResponse()
    {
        $this->responseEvent->expects($this->once())
                            ->method('getResponse')
                            ->will($this->returnValue($this->response));

        $this->response->expects($this->once())
                       ->method('setStatusCode');

        /** @var MaintenanceListener $listener */
        $listener = $this->getListener($this->createRequestMatcher(array()), false, array());

        $listener->onKernelResponse($this->responseEvent);
    }

    /**
     * @return array
     */
    public function loadAllowedIpsProvider()
    {
        return array(
            array(
                array('192.168.56.1'),
                '192.168.56.1',
            ),
            array(
                array('10.5.232.0/24'),
                '10.5.232.0',
            ),
            array(
                array('10.5.232.0/24'),
                '10.5.232.255',
            ),
        );
    }

    /**
     * @return array
     */
    public function loadNotAllowedIpsProvider()
    {
        return array(
            array(
                array('192.168.56.1'),
                '192.168.56.2',
            ),
            array(
                array('10.5.232.0/24'),
                '10.5.233.0',
            ),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\RequestMatcher $matcher
     * @param $debug
     * @param $ipsAllowed
     *
     * @return MaintenanceListener
     */
    private function getListener(RequestMatcher $matcher, $debug, $ipsAllowed)
    {
        return new MaintenanceListener(array(
            $this->controller,
            'showAction',
        ), $matcher, $debug, $ipsAllowed);
    }

    /**
     * @param $ipsAllowed
     *
     * @return RequestMatcher
     */
    private function createRequestMatcher($ipsAllowed)
    {
        return new RequestMatcher(null, null, null, $ipsAllowed);
    }
}
