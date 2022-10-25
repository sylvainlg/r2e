<?php

namespace App\Entity;

/**
 * Enum des status possible des feeds
 */
enum FEED_STATUS: string
{
    case OK = 'OK';
    case KO = 'KO';
    case UNREACHABLE = 'UNREACH';
    case PARSE_FAIL = 'PARSEFAIL';
    case UNKNOWN_ERROR = 'UNKNOWN';
}
