<?php

namespace Pogodi;

use Nette\Object;

class Environment extends Object
{

    const DEFAULT_CACHE_TIME = "1 hour";

    /**
     * @var string
     */
    private $sCdn;

    /**
     * @var string
     */
    private $sScudUrl;

    /**
     * @var string
     */
    private $sCacheTime;

    /**
     * @param string $sCdnUrl
     * @param string $sScudUrl
     * @param string $sCacheTime
     */
    public function __construct($sCdnUrl, $sScudUrl, $sCacheTime = self::DEFAULT_CACHE_TIME)
    {
        $this->sCdn = $sCdnUrl;
        $this->sScudUrl = $sScudUrl;
        $this->sCacheTime = $sCacheTime;
    }

    /**
     * @return string
     */
    public function getCdn()
    {
        return $this->sCdn;
    }

    /**
     * @return string
     */
    public function getScudUrl()
    {
        return $this->sScudUrl;
    }

    /**
     * @return string
     */
    public function getCacheTime()
    {
        return $this->sCacheTime;
    }

}