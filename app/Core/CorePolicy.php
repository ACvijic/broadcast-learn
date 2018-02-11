<?php

namespace App\Core;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\Adminuser,
    App\Models\User;

/**
 *  Policy class 
 *
 *  @category   Policy
 *  @package    Push-platform
 *  @author     Aleksa Cvijić <aleksa.cvijic@cubes.rs>
 *  @author     Aleksandar Stanojević <aleksandar.stanojevic@cubes.rs>
 *  @copyright  2015-2017 Cubes d.o.o.
 *  @version    1.0.0
 *  @link       http://push.cubes.rs/
 *  @since      Class available since Release 1.2.0
 */
class CorePolicy
{
    use HandlesAuthorization;
    
    /**
     *  The attributes used to indicate ownership over a resource
     *
     *  @var    array
     *  @access protected
     */
    protected $ownershipAttributes = [
        /** A **/
        'author',
        'author_id',
        /** C **/
        'created_by_user',
        'created_by_user_id',
        'creator',
        'creator_id',
        'crud',
        'crud_id',
        'crud_by_user',
        'crud_by_user_id',
        /** M **/
        'maker',
        'maker_id',
        /** O **/
        'operator',
        'operator_id',
        'originator',
        'originator_id',
        'owner',
        'owner_id',
        /** P **/
        'producer',
        'producer_id',
        /** U **/
        'user',
        'user_id',
    ];
    
    /**
     *  Determines whether the user can access some resource
     *
     *  @param  User $user
     *  @return boolean
     *  @access public
     */
    public function before(User $user)
    {
        if ($user->role == Adminuser::ROLE_ADMINISTRATOR) {
            return true;
        }
    }
    
    /**
     *  Determines whether the user is the owner of the given resource, so that 
     *  he can create, update or delete it
     *
     *  @param  App\Models\User $user
     *  @param  object          $resource
     *  @param  array|string    $exceptions         attributes that are present 
     *                                              in $ownershipAttributes 
     *                                              that we wish not to be 
     *                                              included in the policies 
     *                                              consideration
     * 
     *  @param  array|string    $overridingRoles    roles we wish to be excused 
     *                                              from the policies 
     *                                              consideration
     *  @return boolean
     *  @access public
     */
    public function ownerCrud(
        User $user, 
        $resource, 
        $exceptions         = null, 
        $overridingRoles    = null,
        $parentRelation     = null
    ) {   
        if(!empty($exceptions)) {
            $this->setOwnershipAttributes($exceptions);
        }
        $userId = $user->id;
        $userRole = $user->role;
        $ownershipAttributes = $this->getOwnershipAttributes();
        
        if(!empty($overridingRoles)) {
            if(
                is_array($overridingRoles) && 
                in_array($userRole, $overridingRoles)
            ) {
                return true;
            }
            
            if(is_string($overridingRoles) && $userRole == $overridingRoles) {
                return true;
            }
        }
        
        foreach($ownershipAttributes as $attribute) {
            $resourceOwnerId = $resource->$attribute;
            
            if(!empty($parentRelation) && !empty($resource->$parentRelation)) {
                $resourceOwnerId = $resource->$parentRelation->$attribute;
            }
            
            if(
                !empty($resourceOwnerId) && 
                $resourceOwnerId == $userId
            ) {
                return true;
            }
        }
    }
    
    /**
     *  @return array
     *  @access protected
     */
    protected function getOwnershipAttributes() 
    {
        return $this->ownershipAttributes;
    }
    
    /**
     *  @param  array|string    $exceptions attributes that are present 
     *                                      in $ownershipAttributes 
     *                                      that we wish not to be 
     *                                      included in the policies 
     *                                      consideration when authorizing
     *  @return void
     *  @access protected
     */
    protected function setOwnershipAttributes($exceptions) 
    {
        if(is_array($exceptions)) {
            $this->ownershipAttributes = array_diff(
                $ownershipAttributes, 
                $exceptions
            );
        }
        if(is_string($exceptions)) {
            foreach($this->ownershipAttributes as $key => $attribute) {
                if (
                    (
                        $key = array_search(
                            $exceptions, 
                            $this->ownershipAttributes
                        )
                    ) !== false
                ) {
                    unset($this->ownershipAttributes[$key]);
                }
            }
        }
    }
}