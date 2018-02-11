<?php

namespace App\Core;

use Validator;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 *  Model class 
 *
 *  @category   Model
 *  @package    Push-platform
 *  @author     Đorđe Stojiljković <djordje.stojiljkovic@cubes.rs>
 *  @author     Aleksa Cvijić <aleksa.cvijic@cubes.rs>
 *  @author     Aleksandar Stanojević <aleksandar.stanojevic@cubes.rs>
 *  @copyright  2015-2017 Cubes d.o.o.
 *  @version    1.2.0 One of the first classes implemented.
 *  @link       http://push.cubes.rs/
 *  @since      Class available since Release 1.0.0
 */
class Model extends EloquentModel implements Constants
{
    /**
     * Fields used when modifying a resource
     *
     * @var array
     */
    protected static $modifyFields = [
        'delete' => 'deleted',
        'hide'   => 'status',
        'show'   => 'status',
        'status' => 'status'
    ];
    
    /**
     *  The attributes used for storing references to images
     *
     *  @var    array
     *  @access protected
     */
    protected $imageFields = [
        /** C **/
        'capture',
        'capture_id',
        /** D **/
        'drawing',
        'drawing_id',
        /** I **/
        'image',
        'image_id',
        'icon',
        'icon_id',
        'illustration',
        'illustration_id',
        /** P **/
        'painting',
        'painting_id',
        'photo',
        'photo_id',
        'photograph',
        'photograph_id',
        'picture',
        'picture_id',
        'portrait',
        'portrait_id',
        /** S **/
        'selfie',
        'selfie_id',
        'sketch',
        'sketch_id',
        'snapshot',
        'snapshot_id',
        /** V **/
        'vignete',
        'vignete_id',
    ];

    /**
     * Object Field Validation rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Page & PageContent Field Errors on Validation fails
     *
     * @var array
     */
    protected $errors;

    /**
     * Validate method used to validate form data.
     *
     * @param $data
     * @return bool
     */
    public function validate($data)
    {
        $validator = Validator::make($data, $this->rules);
        if ($validator->fails()) {
            $this->errors = $validator->errors();
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function errors()
    {
        return $this->errors();
    }
    
    /**
     *  Updates model and stores related data in DB 
     * 
     *  It relies on two facts: that validation has occurred before it is 
     *  called & that the model upon which it is called has a declared fillable 
     *  variable.
     * 
     *  @param  Request     $request
     *  @param  string      $action     tells us which action to take when 
     *                                  storing
     *  @param  callable    $callable   method to be executed on the request 
     *                                  object before storing
     *  @return mixed
     *  @access public
     */
    public function store(
        Request $request, 
        $action, 
        callable $callable = null
    ) {
        if (!empty($callable) && is_callable($callable)) {
            $request = $callable($request);
        }
        
        return DB::transaction(function () use ($request, $action)
        {
            $formData = $this->getFormData($request);
            
            $model = self::$action($formData);
            return $model;
        }, 2);
    }
    
    /**
     *  Changes the status of or deletes an existing Model
     * 
     *  @param  string  $action the string that tells us which 
     *                          action to take while modifying
     *  @return void
     *  @access public
     */
    public function modify($action, $attribute = null) 
    {
        return DB::transaction(function () use ($action, $attribute)
        {
            if(
                $action == self::DELETE || 
                $this->modifyFields[$action] == self::DELETE
            ) {
                $this->delete();
                return $this;
            }
            
            if(
                $action == self::DESTROY || 
                $this->modifyFields[$action] == self::DESTROY
            ) {
                $imageFields = array_intersect(
                    $this->getFillable(), 
                    $this->getImageFields()
                );
                
                foreach($imageFields as $imageField) {
                    $this->$imageField = NULL;
                }
                
                $this->save();
                return $this;
            }
            
            if(empty($attribute)) {
                $attribute = $this->modifyFields[$action];
            }
            
            $oldAttributeValue = $this->$attribute;
            $this->$attribute = (int) !$oldAttributeValue;
            $this->save();
            return $this;
        }, 2);
    }
    
    /**
     *  Finds only relevant data from request, using the models fillable 
     *  attribute
     * 
     *  @param  Request $request
     *  @return void
     *  @access protected
     */
    private function getFormData(Request $request) 
    {
        $keys = array_intersect(
            $this->getFillable(), 
            array_keys($request->all())
        );
        
        return $request->only($keys);
    }
    
    public static function getModifyFields() 
    {
        return self::$modifyFields;
    }

    public function getImageFields() 
    {
        return $this->imageFields;
    }

    public static function setModifyFields($modifyFields) 
    {
        self::$modifyFields = $modifyFields;
        return self;
    }

    public function setImageFields($imageFields) 
    {
        $this->imageFields = $imageFields;
        return $this;
    }
}