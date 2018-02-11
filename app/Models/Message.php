<?php

namespace App\Models;

use App\Core\Model;

use App\Policies\Interfaces\AdminPolicyInterface;
//use App\Policies\Interfaces\ClientPolicyInterface;

/**
 *  @category   Model
 *  @package    Broadcast-learn
 *  @author     Aleksa Cvijić <aleksa.cvijic@cubes.rs>
 *  @copyright  2015-2018 Cubes d.o.o.
 *  @version    1.0.0 One of the first classes implemented.
 *  @link       http://broadcast.learn/
 *  @since      Class available since Release 1.0.0
 */
class Message extends Model implements AdminPolicyInterface
{
    /**
     *  The table associated with the model
     *
     *  @var string
     *  @access protected
     */
    protected $table = 'messages';
    
    /**
     *  The columns in DB that should be interpreted as date-time objects
     * 
     *  @var    array
     *  @access protected
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     *  The attributes that are mass-assignable
     *
     *  @var    array
     *  @access protected
     */
    protected $fillable = [
        'message',
    ];
    
    /**
     *  Fields used when modifying an Message
     *
     *  @var array
     *  @access protected
     */
    protected static $modifyFields = [
        'hide'   => 'push_messages_status',
        'show'   => 'push_messages_status',
        'delete' => 'delete'
    ];
    
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AgeScope);
    }
    
    /* ========= Mutators & Accessors ========= */
    // EXAMPLES:
    /**
     *  Mutates tags attribute on get
     * 
     *  It's called whenever you try and get this attribute from a row in the 
     *  table. Made for parsing the value you want to get from formated string 
     *  (e.g. '#1#,#2#,#3#') to an array.
     * 
     *  @param  string $value 
     *  @return array
     *  @access public
     */
    function getTagsAttribute($value) 
    {
        if(!empty($value)){
           $value = rtrim(ltrim($value, '#'), '#');
            return explode('#,#', $value);
        }
        return NULL;
    
    }
    
    /**
     *  Mutates tags attribute on set
     * 
     *  It's called whenever you try and set this attribute in a row in the 
     *  table. Made for parsing the value you want to set from format array to 
     *  a string format as: '#1#,#2#,#3#'.
     *  
     *  @param  array $value
     *  @return string
     *  @access  public
     */
    function setTagsAttribute($value) 
    {
        $this->attributes['tags'] = '#' . implode('#,#', $value) . '#';
    }
    
    /**
     *  Points to User related to this Message
     * 
     *  @return object
     *  @access public
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}