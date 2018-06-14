<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * ValidationSettings
 */
class ValidationSettings
{
    /**
     * @var string
     */
    private $loginpage;

    /**
     * @var string
     */
    private $validationpage;

    /**
     * @var boolean
     */
    private $validation;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set loginpage
     *
     * @param string $loginpage
     *
     * @return ValidationSettings
     */
    public function setLoginpage($loginpage)
    {
        $this->loginpage = $loginpage;

        return $this;
    }

    /**
     * Get loginpage
     *
     * @return string
     */
    public function getLoginpage()
    {
        return $this->loginpage;
    }

    /**
     * Set validationpage
     *
     * @param string $validationpage
     *
     * @return ValidationSettings
     */
    public function setValidationpage($validationpage)
    {
        $this->validationpage = $validationpage;

        return $this;
    }

    /**
     * Get validationpage
     *
     * @return string
     */
    public function getValidationpage()
    {
        return $this->validationpage;
    }

    /**
     * Set validation
     *
     * @param boolean $validation
     *
     * @return ValidationSettings
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Get validation
     *
     * @return boolean
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
