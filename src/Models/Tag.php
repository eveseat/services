<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 03/03/2017
 * Time: 13:46
 */

namespace Seat\Services\Models;


use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['object_type', 'object_id', 'name'];
}