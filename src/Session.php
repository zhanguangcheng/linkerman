<?php

namespace Linkerman;

class Session extends \Workerman\Protocols\Http\Session
{
    public function setId($session_id): void
    {
        $this->_sessionId = $session_id;
    }

    public function setData(array $data): void
    {
        $this->_data = $data;
        $this->_needSave = true;
    }
}