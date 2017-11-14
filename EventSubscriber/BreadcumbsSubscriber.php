<?php

namespace JIMP\BreadcrumbsBundle\EventSubscriber;

use JIMP\BreadcrumbsBundle\Service\BreadcrumbsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\RouterInterface;

class BreadcumbsSubscriber implements EventSubscriberInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var BreadcrumbsService
     */
    protected $service;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * BreadcumbsSubscriber constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->service = $this->container->get('jimp_breadcrumbs');
        $this->router = $this->container->get('router');
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        $params = $this->router->match($request->getPathInfo());

        if ($route)
            $this->service->addBreadcrumbs($route, $params);
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'kernel.controller' => 'onKernelController'
        );
    }
}