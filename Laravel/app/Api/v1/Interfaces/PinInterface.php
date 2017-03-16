<?php

namespace App\Api\v1\Interfaces;

interface PinInterface
{
    public function create();
    public function update($id);
    public function getOne($id);
    public function delete($id);
    public function getFromUser($user_id);

    // inner method
    public function getRawPinData($id); // return db resource object
    public function formatRawPinData($obj); // db res obj --> json object
}