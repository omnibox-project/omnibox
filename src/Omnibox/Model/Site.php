<?php
namespace Omnibox\Model;

class Site
{
    public $name;
    public $domain;
    public $directory;
    public $webroot;
    public $share;
    public $server;

    public function __construct($name = null, $domain = null, $directory = null, $webroot = null, $alias = null, $webconfig = 'default', $share = 0, $server = 'nginx')
    {
        $this->directory = $directory;
        $this->domain = $domain;
        $this->name = $name;
        $this->webroot = $webroot;
        $this->alias = $alias;
        $this->webconfig = $webconfig;
        $this->share = $share;
        $this->server = $server;
    }

    /**
     * @return int
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @param int $share
     */
    public function setShare($share)
    {
        $this->share = $share;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param mixed $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getWebroot()
    {
        return $this->webroot;
    }

    /**
     * @param mixed $webroot
     */
    public function setWebroot($webroot)
    {
        $this->webroot = $webroot;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getWebconfig()
    {
        return $this->webconfig;
    }

    /**
     * @param string $webconfig
     */
    public function setWebconfig($webconfig)
    {
        $this->webconfig = $webconfig;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    public function toArray($id = null)
    {
        if ($id === null) {
            return [
                'name' => $this->getName(),
                'domain' => $this->getDomain(),
                'directory' => $this->getDirectory(),
                'webroot' => $this->getWebroot(),
                'alias' => $this->getAlias(),
                'webconfig' => $this->getWebconfig(),
                'share' => $this->getShare(),
                'server' => $this->getServer(),
            ];
        } else {
            return [
                'id' => $id,
                'name' => $this->getName(),
                'domain' => $this->getDomain(),
                'directory' => $this->getDirectory(),
                'webroot' => $this->getWebroot(),
                'alias' => $this->getAlias(),
                'webconfig' => $this->getWebconfig(),
                'share' => $this->getShare(),
                'server' => $this->getServer(),
            ];
        }
    }
}
