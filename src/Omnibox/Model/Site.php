<?php
namespace Omnibox\Model;

class Site
{
    var $name;
    var $domain;
    var $directory;
    var $webroot;

    function __construct($name = null, $domain = null, $directory = null, $webroot = null, $alias = null, $webconfig = 'default')
    {
        $this->directory = $directory;
        $this->domain = $domain;
        $this->name = $name;
        $this->webroot = $webroot;
        $this->alias = $alias;
        $this->webconfig = $webconfig;
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

    public function toArray($id = null)
    {
        if ($id === null) {
            return array(
                'name' => $this->getName(),
                'domain' => $this->getDomain(),
                'directory' => $this->getDirectory(),
                'webroot' => $this->getWebroot(),
                'alias' => $this->getAlias(),
                'webconfig' => $this->getWebconfig(),
            );
        } else {
            return array(
                'id' => $id,
                'name' => $this->getName(),
                'domain' => $this->getDomain(),
                'directory' => $this->getDirectory(),
                'webroot' => $this->getWebroot(),
                'alias' => $this->getAlias(),
                'webconfig' => $this->getWebconfig(),
            );
        }
    }
}
