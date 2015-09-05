<?php

namespace Pogodi;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\Json;

class Api extends Object
{

    const
        POST = 'POST',
        GET = 'GET',

        ALL_APPLICATION_CACHE_KEY = 'all_applications',

        MAIN_APPLICATION = 'scud',
        ALL_APPLICATIONS_METHOD = 'APPLICATIONS_GetAll';

    /**
     * @var Environment
     */
    protected $oEnvironment;

    /**
     * @var Cache
     */
    protected $oCache;

    /**
     * @var array
     */
    protected $aApplications;

    /**
     * @param Environment $oEnvironment
     * @param IStorage $oStorage
     * @throws BadApplicationException
     */
    public function __construct(Environment $oEnvironment, IStorage $oStorage)
    {
        $this->oEnvironment = $oEnvironment;
        $this->oCache = new Cache($oStorage);

        $this->aApplications = $this->oCache->load(self::ALL_APPLICATION_CACHE_KEY);
        if (NULL === $this->aApplications) {
            $this->loadApplications();
        }
    }

    /**
     * @param string $sApplicationName
     * @param string $sUrl
     * @param array $aParams
     * @return array
     * @throws BadApplicationException
     * @throws \Nette\Utils\JsonException
     */
    public function call($sApplicationName, $sUrl, $aParams = [])
    {
        $sUrlToCall = $this->aApplications[$sApplicationName] . "/" . $sUrl;
        if (true === empty($sUrlToCall)) {
            throw new BadApplicationException("Application with name '{$sUrlToCall}' not found.");
        }

        $sResponse = $this->sendRequest(
            (count($aParams) > 0) ? self::POST : self::GET,
            $sUrlToCall,
            $aParams
        );

        return Json::decode($sResponse, TRUE);
    }

    /**
     * @param string $sMethod
     * @param string $sUrl
     * @param array $aParams
     * @return string
     */
    protected function sendRequest($sMethod, $sUrl, $aParams = [])
    {
        $rCurl = curl_init();
        if (self::GET === $sMethod) {
            curl_setopt_array($rCurl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $sUrl,
            ]);
        } elseif (self::POST === $sMethod) {
            $sFields = "";
            foreach ($aParams as $key => $value) {
                $sFields .= $key . '=' . $value . '&';
            }
            rtrim($sFields, '&');
            curl_setopt($rCurl, CURLOPT_URL, $sUrl);
            curl_setopt($rCurl, CURLOPT_POST, count($aParams));
            curl_setopt($rCurl, CURLOPT_POSTFIELDS, $sFields);
        }

        $sResp = curl_exec($rCurl);
        return $sResp;
    }

    /**
     * Load all environment applications
     * @throws BadApplicationException
     */
    protected function loadApplications()
    {
        $this->aApplications = [];
        $this->aApplications[self::MAIN_APPLICATION] = $this->oEnvironment->getScudUrl();
        $aApps = $this->call(self::MAIN_APPLICATION, self::ALL_APPLICATIONS_METHOD);
        foreach ($aApps as $aApp) {
            $this->aApplications[$aApp['name']] = $aApp['url'];
        }

        $this->oCache->save(self::ALL_APPLICATION_CACHE_KEY, $this->aApplications, [
            Cache::EXPIRE => $this->oEnvironment->getCacheTime(),
        ]);
    }

}