<?php

namespace FluxIliasRestApi\Service\UserMail;

use ilDBConstants;

trait UserMailQuery
{

    private function getUserMailQuery(?int $user_id = null, ?string $status = null, bool $count = false) : string
    {
        $wheres = [];

        if ($user_id !== null) {
            $wheres[] = "user_id=" . $this->ilias_database->quote($user_id, ilDBConstants::T_INTEGER);
        }

        if ($status !== null) {
            $wheres[] = "m_status=" . $this->ilias_database->quote($status, ilDBConstants::T_TEXT);
        }

        return "SELECT " . ($count ? "COUNT(mail_id) AS count" : "*") . "
FROM mail
" . (!empty($wheres) ? "WHERE " . implode(" AND ", $wheres) : "");
    }
}
