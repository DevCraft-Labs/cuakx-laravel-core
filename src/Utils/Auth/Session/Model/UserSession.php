<?php

namespace Cuakx\Core\Utils\Auth\Session\Model;

use Cuakx\Core\Constant\CommonConstants;
use Cuakx\Core\Utils\Console;
use DateInterval;
use DateMalformedIntervalStringException;
use DateTime;

class UserSession
{
    private int $_user_id;
    private string $_role_id;
    private int $_access_id;
    private string $_organization_id;
    private string $_user_name;
    private string $_organization_name;
    private string $_created_at;
    private int $_created_at_timestamp;
    private string $_expired_at;
    private int $_expired_at_timestamp;

    public function __construct(int $user_id,
                                string $role_id,
                                int $access_id,
                                string $organization_id,
                                string $user_name,
                                string $organization_name,
                                DateTime $issued_at){
        $this->user_id = $user_id;
        $this->role_id = $role_id;
        $this->access_id = $access_id;
        $this->organization_id = $organization_id;
        $this->user_name = $user_name;
        $this->organization_name = $organization_name;
        $this->setIssuedAt($issued_at);
        try {
            $this->setExpiredAt($issued_at->add(new DateInterval("PT" . CommonConstants::SESSION_TIMEOUT . "S")));
        } catch (DateMalformedIntervalStringException $e) {
            Console::writeLine(
                message: "Expired at date malfomed. Token expirity not built",
                type: 'w'
            );
        }
    }

    public int $user_id {
      set (int $value) { $this->_user_id = $value; }
      get { return $this->_user_id; }
    }

    public string $role_id {
        set (string $value) { $this->_role_id = $value; }
        get { return $this->_role_id; }
    }

    public int $access_id {
        set (int $value) { $this->_access_id = $value; }
        get { return $this->_access_id; }
    }

    public string $organization_id {
        set (string $value) { $this->_organization_id = $value; }
        get { return $this->_organization_id; }
    }

    public string $user_name {
        set (string $value) { $this->_user_name = $value; }
        get { return $this->_user_name; }
    }

    public string $organization_name {
        set (string $value) { $this->_organization_name = $value; }
        get { return $this->_organization_name; }
    }

    public string $issued_at { get { return $this->_created_at; } }
    public string $expired_at { get { return $this->_expired_at; } }
    public int $issued_at_timestamp { get { return $this->_created_at_timestamp; } }
    public int $expired_at_timestamp { get { return $this->_expired_at_timestamp; } }

    public function setIssuedAt(DateTime $dateTime): void {
        $this->_created_at_timestamp = $dateTime->getTimestamp();
        $this->_created_at = $dateTime->format(CommonConstants::DATE_DEFAULT_FORMAT);
    }

    public function setExpiredAt(DateTime $dateTime): void {
        $this->_expired_at_timestamp = $dateTime->getTimestamp();
        $this->_expired_at = $dateTime->format(CommonConstants::DATE_DEFAULT_FORMAT);
    }

}