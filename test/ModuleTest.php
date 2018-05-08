<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\SendResponseListener;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\Listener\ApiProblemListener;
use ZF\ApiProblem\Listener\SendApiProblemResponseListener;

class ModuleTest extends TestCase
{
    public function marshalEventManager()
    {
        $r = new ReflectionClass(EventManager::class);
        if ($r->hasMethod('setSharedManager')) {
            $eventManager = new EventManager();
            $eventManager->setSharedManager(new SharedEventManager());
            return $eventManager;
        }
        return new EventManager(new SharedEventManager());
    }

    public function testOnBootstrap()
    {
        $module = new Module();

        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')->will($this->returnCallback([$this, 'serviceLocator']));

        $eventManager = $this->marshalEventManager();
        $event = $this->getMockBuilder(MvcEvent::class)->getMock();

        $application->method('getServiceManager')->willReturn($serviceLocator);
        $application->method('getEventManager')->willReturn($eventManager);
        $event->expects($this->once())->method('getTarget')->willReturn($application);

        $module->onBootstrap($event);
    }

    public function serviceLocator($service)
    {
        switch ($service) {
            case 'ZF\ApiProblem\Listener\ApiProblemListener':
                return new ApiProblemListener();
                break;
            case 'SendResponseListener':
                $listener = $this->getMockBuilder(SendResponseListener::class)->getMock();
                $listener->method('getEventManager')->willReturn(new EventManager());

                return $listener;
                break;
            case SendApiProblemResponseListener::class:
                return new SendApiProblemResponseListener();
            default:
                //
        }
    }
}
