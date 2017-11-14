<?php

namespace AKSW\DiscoverShaclShapes\Service\TwigExtension;

use Saft\Rdf\CommonNamespaces;

class UrlExtension extends \Twig_Extension
{
    protected $commonNamespaces;

    /**
     * @param CommonNamespaces $commonNamespaces
     */
    public function __construct(CommonNamespaces $commonNamespaces)
    {
        $this->commonNamespaces = $commonNamespaces;
    }

    /**
     * Gets called from twig template to extend a given URI.
     *
     * @param string $uri
     * @return string
     */
    public function extend(string $uri) : string
    {
        return $this->commonNamespaces->extendUri($uri);
    }

    /**
     * Gets called from twig template to shorten a given URI.
     *
     * @param string $uri
     * @return string
     */
    public function shorten(string $uri) : string
    {
        return $this->commonNamespaces->shortenUri($uri);
    }

    /**
     * @return array
     */
    public function getFunctions() : array
    {
        return array(
            new \Twig_SimpleFunction('extend', array($this, 'extend')),
            new \Twig_SimpleFunction('shorten', array($this, 'shorten')),
        );
    }
}
