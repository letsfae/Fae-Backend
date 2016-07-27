<?php

namespace App\Api\v1\Interfaces;

interface RefInterface
{
	public static function exists($id);
    public static function ref($id);
    public static function deref($id);

    public static function refByString($str);
    public static function derefByString($str);
    public static function updateRefByString($old_str, $new_str);
    public static function existsByString($str);
}