<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\Message;

/**
 *  @category   Model
 *  @package    Broadcast-learn
 *  @author     Aleksa Cvijić <aleksa.cvijic@cubes.rs>
 *  @copyright  2015-2018 Cubes d.o.o.
 *  @version    1.0.0 One of the first classes implemented.
 *  @link       http://broadcast.learn/
 *  @since      Class available since Release 1.0.0
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     *  The attributes that are mass-assignable
     *
     *  @var    array
     *  @access protected
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     *  The attributes that should be hidden for arrays
     *
     *  @var    array
     *  @access protected
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     *  Points to Messages related to this User
     * 
     *  @return object
     *  @access public
     */
    public function messages() 
    {
        return $this->hasMany(Message::class);
    }
}
