<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 12.09.2018
 * Time: 20:27
 */

namespace Seat\Services\Models;


use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

class HistoricalPrices extends Model
{
    use HasCompositePrimaryKey;

    protected static $unguarded = true;

    protected $table = 'historical_prices';

    protected $primaryKey = ['type_id'];

    public $incrementing = false;

    protected $dates = ['created_at', 'updated_at'];

}