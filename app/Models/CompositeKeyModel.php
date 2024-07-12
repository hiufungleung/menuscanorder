<?php

namespace App\Models;

use CodeIgniter\Model;

class CompositeKeyModel extends Model
{
    protected $key1 = '';
    protected $key2 = '';

    public function findByCompositeKey($value1, $value2)
    {
        return $this->where($this->key1, $value1)
                    ->where($this->key2, $value2)
                    ->first();
    }

    public function updateByCompositeKey($value1, $value2, $data)
    {
        return $this->where($this->key1, $value1)
                    ->where($this->key2, $value2)
                    ->update($data);
    }

    public function deleteByCompositeKey($value1, $value2)
    {
        return $this->where($this->key1, $value1)
                    ->where($this->key2, $value2)
                    ->delete();
    }
}
