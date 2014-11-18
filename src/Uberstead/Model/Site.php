<?php
namespace Uberstead\Model;

class Site
{
    var $name;
    var $domain;
    var $directory;
    var $webroot;

    function __construct($name = '', $domain = '', $directory = '', $webroot = '')
    {
        $this->directory = $directory;
        $this->domain = $domain;
        $this->name = $name;
        $this->webroot = $webroot;
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

    public function toArray($id = null)
    {
        if ($id === null) {
            return array(
                'name' => $this->getName(),
                'domain' => $this->getDomain(),
                'directory' => $this->getDirectory(),
                'webroot' => $this->getWebroot()
            );
        } else {
            return array(
                'id' => $id,
                'name' => $this->getName(),
                'domain' => $this->getDomain(),
                'directory' => $this->getDirectory(),
                'webroot' => $this->getWebroot()
            );
        }
    }
}
