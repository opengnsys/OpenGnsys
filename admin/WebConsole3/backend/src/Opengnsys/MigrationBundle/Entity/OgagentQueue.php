<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * OgagentQueue
 */
class OgagentQueue
{
    /**
     * @var int
     */
    private $clientid;

    /**
     * @var \DateTime|null
     */
    private $exectime;

    /**
     * @var string|null
     */
    private $operation;

    /**
     * @var int
     */
    private $id;


    /**
     * Set clientid.
     *
     * @param int $clientid
     *
     * @return OgagentQueue
     */
    public function setClientid($clientid)
    {
        $this->clientid = $clientid;

        return $this;
    }

    /**
     * Get clientid.
     *
     * @return int
     */
    public function getClientid()
    {
        return $this->clientid;
    }

    /**
     * Set exectime.
     *
     * @param \DateTime|null $exectime
     *
     * @return OgagentQueue
     */
    public function setExectime($exectime = null)
    {
        $this->exectime = $exectime;

        return $this;
    }

    /**
     * Get exectime.
     *
     * @return \DateTime|null
     */
    public function getExectime()
    {
        return $this->exectime;
    }

    /**
     * Set operation.
     *
     * @param string|null $operation
     *
     * @return OgagentQueue
     */
    public function setOperation($operation = null)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Get operation.
     *
     * @return string|null
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
