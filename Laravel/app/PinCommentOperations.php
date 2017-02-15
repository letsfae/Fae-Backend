<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PinCommentOperations extends Model
{
    protected $table = 'pin_comment_operations';
    public function updateVoteTimestamp()
    {
        $this->vote_timestamp = $this->freshTimestamp();
        return $this->save();
    }
}
