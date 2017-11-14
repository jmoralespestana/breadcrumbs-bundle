<?php

namespace JIMP\BreadcrumbsBundle\Twig\Extension;


class BreadcrumbExtension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \JIMP\BreadcrumbsBundle\Service\BreadcrumbsService
     */
    protected $service;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->service = $container->get("jimp_breadcrumbs");
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'jimp_breadcrumbs',
                array($this, 'renderBreadcrumbs'),
                array('is_safe' => array('html'))
            )
        );
    }

    public function renderBreadcrumbs()
    {
        $router = $this->container->get('router');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $route = $request->get('_route');

        $containerClass = $this->container->getParameter('jim_breadcrumbs.container_class');
        $separatorClass = $this->container->getParameter('jim_breadcrumbs.separator_class');

        try {
            $params = $router->match(urldecode($request->getPathInfo()));
        } catch (\RuntimeException $e) {
            return;
        }

        return $this->container->get("templating")->render(
            "JIMPBreadcrumbsBundle:breadcrumbs:breadcrumbs.html.twig",
            array(
                'breadcrumbs' => $this->service->getBreadcrumbs((string)$route, $params),
                'separatorClass' => $separatorClass,
                'containerClass' => $containerClass
            )
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'breadcrumbs';
    }

}