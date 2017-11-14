<?php


namespace JIMP\BreadcrumbsBundle\Service;

use JIMP\BreadcrumbsBundle\Model\Breadcrumb;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class BreadcrumbsService
{
    const TWIG_TAG = "# ?\{([^/}]+)\} ?#";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Used as a dict to save/cache Breadcrumb for route and parameter combinations
     * @var array
     */
    private $cache = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter($router)
    {
        return $this->router = $router;
    }

    /**
     * @param $routeName
     * @param array $params
     * @return array
     */
    public function createBreadCrumbs($routeName, $params = array())
    {
        $parents = $this->getParents($routeName);
        $breadcrumbs = array();

        foreach (array_merge($parents, array($routeName)) as $current) {
            $breadcrumbs[$current] = new Breadcrumb(
                $this->getLabel($current, $params),
                $this->getUrl($current, $params)
            );
        }
        return $breadcrumbs;
    }

    /**
     * @param $routeName
     * @return array
     */
    public function getParents($routeName)
    {
        $parents = array();
        $parent = $this->getParent($routeName);

        while ($parent && !array_key_exists($parent, $parents)) {
            $parents[$parent] = count($parents);
            $parent = $this->getParent($parent);
        }

        return array_reverse(array_flip($parents));
    }


    /**
     * @param string $routeName
     * @return null
     */
    public function getParent(string $routeName)
    {
        $route = $this->getRoute($routeName);

        if ($route && $route->hasDefault('parent')) {
            $parent = $route->getDefault('parent');
            //$routeParent = $this->getRoute($parent);

            return ($this->getRoute($parent) ? $parent : null);
        }
        return null;
    }


    /**
     * @param string $routeName
     * @return \Symfony\Component\Routing\Route
     */
    public function getRoute($routeName)
    {
        if (!is_string($routeName)) {
            throw new \InvalidArgumentException(__FUNCTION__ . '() only accepts route name as string');
        }

        return $this->router->getRouteCollection()->get($routeName);
    }


    /**
     * @param $routeName
     * @param array $params
     * @return mixed
     */
    public function getLabel($routeName, array $params = array())
    {
        if (($route = $this->getRoute($routeName)) && $route->hasDefault('label')) {
            $label = $route->getDefault('label');

            if ($route->hasDefault('_locale')) {
                $locale = $route->getDefault('_locale');

                $label = $this->container->get('translator')->trans($label, array(), 'breadcrumbs',
                    $locale ?? $this->container->getParameter('kernel.default_locale')
                );
            }

            return $this->applyParams($label, $this->getApplicableParams($routeName, $params, true));
        }

        return $routeName;
    }


    /**
     * @param $label
     * @param array $params
     * @return mixed
     */
    protected function applyParams($label, array $params)
    {
        $patterns = array_map(
            function ($tag) {
                return "/\{${tag}\}/";
            },
            array_keys($params)
        );

        return preg_replace($patterns, array_values($params), $label);
    }


    /**
     * @param string $routeName
     * @param array $params
     * @param bool $fromLabel
     * @return array
     */
    private function getApplicableParams($routeName, array $params, $fromLabel = false)
    {
        if ($route = $this->getRoute($routeName)) {

            // If doesn't match it have not params
            if (!preg_match(self::TWIG_TAG, $route->getPath())) {
                return array();
            }

            // Ensure we have requirements
            if (!$reqs = $route->getRequirements()) {
                $template = $fromLabel
                    ? $route->getDefault('label')
                    : $route->getPath();
                preg_match_all(self::TWIG_TAG, $template, $matches);

                $reqs = array_flip($matches[1]);
            }

            // Get default values for missing parameters
            foreach ($route->getDefaults() as $def => $value) {
                if (!array_key_exists($def, $params) && array_key_exists($def, $reqs)) {
                    $params[$def] = $value;
                }
            }

            // Return matched params
            if (!empty($params) && $reqs) {
                return array_intersect_key($params, $reqs);
            }
        }
        return array();
    }


    /**
     * @param string $name
     * @return string
     */
    public function getUrl($name, array $params = array())
    {
        return $this->router->generate($name, $this->getApplicableParams($name, $params));
    }

    /**
     * Adds (and caches) breadcrumbs for route name and parameters.
     *
     * @param string $route
     * @param array $params
     */
    public function addBreadcrumbs($route, array $params = array())
    {
        $applicableParams = $this->getApplicableParams($route, $params);
        if ($bc = $this->getBreadcrumbs($route, $applicableParams, true)) {
            $this->cache[$this->getHash($route, $applicableParams)] = $bc;
        }
    }

    /**
     * Get an array of Breadcrumbs by route name and parameters
     *
     * @param string $name
     * @param array $params
     * @param boolean $caching True when adding breadcrumbs to cache (prevent getting from the cache), false otherwise (default)
     * @return array Array of Breadcrumbs
     */
    public function getBreadcrumbs($name, array $params = array(), $caching = false)
    {
        $hash = $this->getHash($name, $this->getApplicableParams($name, $params));
        if (!$caching && array_key_exists($hash, $this->cache)) {
            return $this->cache[$hash];
        }

        $breadcrumbs = $this->createBreadcrumbs($name, $params);

        $this->cache[$hash] = $breadcrumbs;
        return $breadcrumbs;
    }


    private function getHash($route, $params)
    {
        return hash('sha1', json_encode(array_merge($params, array('route' => $route))));
    }
}