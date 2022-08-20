<?php

namespace RateLimit;

enum IntervalInSeconds: int
{
    case SECOND = 1;
    case MINUTE = 60;
    case HOUR = 3600;
    case DAY = 86400;
}
