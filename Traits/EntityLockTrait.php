<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Traits;

use JasonMx\Components\Exception\LockException;
use JasonMx\Components\Extend\AppController;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait EntityLockTrait
 * @package JasonMx\Components\Traits
 */
trait EntityLockTrait
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean", options={"default":0})
     */
    protected $locked = false;

    /**
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param boolean $locked
     * @return self
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @ORM\PreRemove()
     * @throws
     */
    public function checkLockBeforeRemove()
    {
        if($this->isLocked())
            throw new LockException(AppController::MSG_TEXT_LOCKED);
    }
}