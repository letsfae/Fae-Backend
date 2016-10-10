<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pin_operations extends Model
{
    protected $table = 'pin_operations';
    public function updateSavedTimestamp()
    {
        $this->saved_timestamp = $this->freshTimestamp();
        return $this->save();
    }
    public function updateLikeTimestamp()
    {
        $this->liked_timestamp = $this->freshTimestamp();
        return $this->save();
    }
    protected $fillable = array('user_id', 'pin_id', 'type', 'saved', 'liked');
}