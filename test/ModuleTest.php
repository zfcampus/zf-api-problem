<?php
/**
 * @link      http://github.com/zfcampus/zf-api-problem for the canonical source repository
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


namespace ZF\ApiProblem;

use PHPUnit_Framework_TestCase as TestCase;
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

        $application = $this->getMock(Application::class, [], [], '', false);
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')->will($this->returnCallback([$this, 'serviceLocator']));

        $eventManager = $this->marshalEventManager();
        $event = $this->getMock(MvcEvent::class);

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
                $listener = $this->getMock(SendResponseListener::class);
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
