<?php


namespace JIMP\BreadcrumbsBundle\Model;


class Breadcrumb
{
    /**
     * @var string Label of the breadcrumb
     */
    public $label;

    /**
     * @var string Url of the breadcrumb
     */
    public $url;

    /**
     * @param string $label Label of the breadcrumb
     * @param string $url Url of the breadcrumb
     */
    public function __construct($label, $url = '')
    {
        $this->label = $label;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $tpl = " Object(\n    label => %s\n    url => %s\n)";
        return __CLASS__ . sprintf($tpl, $this->label, $this->url);
    }
}