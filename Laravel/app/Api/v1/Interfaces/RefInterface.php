<?php

namespace App\Api\v1\Interfaces;

interface RefInterface
{
    public static function ref($id);
    public static function deref($id);
}