<?php

namespace Linkerman;

/**
 * @property string|null $sid
 */
class Request extends \Workerman\Protocols\Http\Request
{
    /**
     * @var Session
     */
    public $session = null;

    public function session($id = null): Session|false
    {
        if ($this->session === null) {
            $session_id = $this->sessionId($id);
            if ($session_id === false) {
                return false;
            }
            $this->session = new Session($session_id);
        }
        return $this->session;
    }

    public static function generateSessionId(): string
    {
        return parent::createSessionId();
    }
}