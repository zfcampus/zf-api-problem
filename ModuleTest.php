<?php
/**
 * Creator: adamgrabek
 * Date: 08.06.2016
 * Time: 00:06
 */

namespace ZF\ApiProblem;

use PHPUnit_Framework_TestCase as TestCase;
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

    public function testOnBootstrap()
    {
        $module = new Module();


        $application = $this->getMock(Application::class, [], [], '', FALSE);
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')->will($this->returnCallback([$this, 'serviceLocator']));

        $eventManager = new EventManager(new SharedEventManager());
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
            case SendApiProblemResponseListener::class :
                return new SendApiProblemResponseListener();
            default:
                //
        }
    }
}