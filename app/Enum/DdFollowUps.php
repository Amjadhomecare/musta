<?php

namespace App\Enum;

enum DdFollowUps : int
{
    case FollowedUpSent = 1;
    case CustomerDidNotReply = 2;
    case CustomerReplied = 3;
    case FollowUpManually = 4;

    public function labels(): array
    {
        return [
            self::FollowedUpSent => 'Followed Up Sent',
            self::CustomerDidNotReply => 'Customer Did\'t Reply',
            self::CustomerReplied => 'Customer Replied',
            self::FollowUpManually => 'Follow Up Manually',
        ];
    }



}

