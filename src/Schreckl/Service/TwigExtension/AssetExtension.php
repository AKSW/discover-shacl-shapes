<?php

namespace Schreckl\Service\TwigExtension;

class AssetExtension extends \Twig_Extension
{
    protected $assetsPath;
    protected $url;

    /**
     * @param string $url URL to page
     * @param string $assetsPath Path part from URL to assets folder.
     */
    public function __construct($url, $assetsPath)
    {
        $this->assetsPath = $assetsPath;
        $this->url = $url;
    }

    /**
     * Gets called from twig template to return a path for given file.
     */
    public function getAssetPath($file)
    {
        return $this->url .'/'. $this->assetsPath . $file;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'getAssetPath')),
        );
    }
}
