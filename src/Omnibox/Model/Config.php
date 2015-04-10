<?php
namespace Omnibox\Model;

use Omnibox\Model\Site;

class Config
{
    private $ip;
    private $memory;
    private $cpus;
    private $authorize;
    private $defaultfoldertype;
    private $keys = array();
    private $sites = array();

    public function __construct($array = null)
    {
        if (isset($array['sites'])) {
            foreach ($array['sites'] as $s) {
                $site = new Site(
                    $s['name'],
                    $s['domain'],
                    $s['directory'],
                    $s['webroot'],
                    (isset($s['alias']) ? $s['alias'] : null),
                    (isset($s['webconfig']) ? $s['webconfig'] : 'default')
                );
                $this->addSite($site);
            }
        }

        if (isset($array['keys'])) {
            foreach ($array['keys'] as $key) {
                $this->addKey($key);
            }
        }

        if (isset($array['ip'])) {
            $this->setIp($array['ip']);
        }

        if (isset($array['memory'])) {
            $this->setMemory($array['memory']);
        }

        if (isset($array['cpus'])) {
            $this->setCpus($array['cpus']);
        }

        if (isset($array['authorize'])) {
            $this->setAuthorize($array['authorize']);
        }

        if (isset($array['defaultfoldertype'])) {
            $this->setDefaultfoldertype($array['defaultfoldertype']);
        }
    }


    /**
     * @param Site $site
     */
    public function addSite(Site $site)
    {
        $this->sites[] = $site;
    }
    /**
     * @param $key
     */
    public function addKey($key)
    {
        $this->keys[] = $key;
    }

    /**
     * @return string
     */
    public function getAuthorize()
    {
        return $this->authorize;
    }

    /**
     * @param string $authorize
     */
    public function setAuthorize($authorize)
    {
        $this->authorize = $authorize;
    }

    /**
     * @return string
     */
    public function getCpus()
    {
        return $this->cpus;
    }

    /**
     * @param string $cpus
     */
    public function setCpus($cpus)
    {
        $this->cpus = $cpus;
    }

    /**
     * @return string
     */
    public function getDefaultfoldertype()
    {
        return $this->defaultfoldertype;
    }

    /**
     * @param string $defaultfoldertype
     */
    public function setDefaultfoldertype($defaultfoldertype)
    {
        $this->defaultfoldertype = $defaultfoldertype;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @param array $keys
     */
    public function setKeys($keys)
    {
        $this->keys = $keys;
    }

    /**
     * @return string
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * @param string $memory
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;
    }

    /**
     * @return Site[]
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param array $sites
     */
    public function setSites($sites)
    {
        $this->sites = $sites;
    }

    public function toArray()
    {
        return array(
            'ip' => $this->getIp(),
            'memory' => $this->getMemory(),
            'cpus' => $this->getCpus(),
            'authorize' => $this->getAuthorize(),
            'keys' => $this->getKeys(),
            'defaultfoldertype' => $this->getDefaultfoldertype(),
            'sites' => $this->getSitesArray()
        );
    }

    public function getSitesArray($showIds = false)
    {
        $sites = array();
        foreach ($this->getSites() as $i => $site) {
            if ($showIds === true) {
                $sites[] = $site->toArray($i);
            } else {
                $sites[] = $site->toArray();
            }
        }

        return $sites;
    }
}
