<?php

namespace JIMP\BreadcrumbsBundle\Controller;

use JIMP\BreadcrumbsBundle\Model\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    const TWIG_TAG = "# ?\{([^/}]+)\} ?#";

    public function indexAction(Request $request, $name)
    {

        return $this->render('JIMPBreadcrumbsBundle:Default:index.html.twig');
    }

    public function greetsAction(Request $request, $name)
    {
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        return $this->render('JIMPBreadcrumbsBundle:Default:index.html.twig');
    }


}
