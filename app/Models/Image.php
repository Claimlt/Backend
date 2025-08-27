<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;
    use HasUuids;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'image';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['filename', 'imageable_type'];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    //  UUID settings
    public $incrementing = false;   // no auto increment
    protected $keyType = 'string';  // primary key is a string


}
