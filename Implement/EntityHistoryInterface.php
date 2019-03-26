<?php
/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Implement;

interface EntityHistoryInterface
{
    /** @return string */
    public function getHistoryGroup();

    /** @return string */
    public function getHistoryDesc();
}