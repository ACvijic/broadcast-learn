<?php

namespace App\Core;

use App\Helpers\View\Flashbangs;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use Intervention\Image\ImageManager;

class Controller extends BaseController implements Constants
{
    /**
     * Task & image size constants.
     */
    const TASK_CREATE       = 'create';
    const TASK_UPDATE       = 'update';
    const TASK_DELETE       = 'delete';
    const TASK_VIEW         = 'view';
    const TASK_TRANSLATE    = 'translate';

    const WIDTH_XL  = '1920'; //XL
    const HEIGHT_XL = '1080';

    const WIDTH_L   = '1280'; // L
    const HEIGHT_L  = '800';

    const WIDTH_S   = '600';  // S
    const HEIGHT_S  = '400';

    const ALL_SIZES = [
        '1920, 1080',
        '1280, 800',
        '600, 400'
    ];

    const GD        = 'gd';
    const IMAGICK   = 'imagick';

    /**
     * imagePath property used for defining imagePath and passing it to view.
     *
     * @var string
     */
    protected $imagePath;
    
    /**
     *  Model upon which the controller revolves 
     * 
     *  @var    string 
     *  @access protected
     */
    protected $model;

    /**
     * PagesController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');

        // If path property isset in child class we will share global
        // view accessor for easier image path manipulation and echoing.
        if ($this->imagePath && $this->imagePath !== '') {
            view()->share('image_path', $this->imagePath);
        }
    }

    /**
     * Method checks if request task is as defined.
     *
     * @param  string $task
     * @return boolean
     * @throws \Exception
     */
    public function isTask($task = '')
    {
        if (request()->task === null) {
            throw new \Exception('Task property is not found in current request.');
        }

        if (trim(request()->task) === '') {
            throw new \Exception('Task is not defined or is defined as empty string/object');
        }

        if ($task === request()->task) {
            return true;
        }

        return false;
    }

    /**
     * Method makeImage used for building image with Intervention image library.
     *
     * @param  string $field  | Name of image input field used in form
     * @param  string $path   | Full path for image to be built, resized. ('public/foo.jpg)
     * @param  string $size   | Image width-X-height size in this format '1280, 600' .
     * @param  bool   $scale  | Auto scale image to fit best sizes.
     * @param  string $driver | Image manipulation driver to be used instead of default gd library.
     * @param  string $name   | Name of output image to be saved
     * @throws \Exception
     * @return mixed
     */
    protected function makeImage($field = 'photo', $path = '', $size = null, $scale = false, $driver = self::GD, $name = '')
    {
        // Get current request object
        $request = request();

        // If path argument is null or empty string we will throw exception.
        if (!$path || $path === '') {
            throw new \Exception('You must provide full path to image resource. Path can\'t be empty');
        }

        // If provided path is not absolute we 'll try to convert it to absolute.
        if (!$this->isAbsolutePath($path)) {
            $path = public_path($path);
        }

        // Path remove unnecessary slashes from string
        $path = preg_replace('/([^:])(\/{2,})/', '$1/', $path);

        // If image doesn't exist or path is not valid, not accessible we will throw exception.
        if (!file_exists($path)) {
            throw  new \Exception('Provided image path: '.$path.' is not valid or doesn\'t exist.');
        }

        // If driver !isset we will set it to default image manipulation library.
        if ($driver === '' || !$driver) {
            $driver = self::GD;
        }

        // System image manipulation driver setting
        $manager = new ImageManager(['driver' => $driver]);
        $inputName = (string) $field;

        // Check if image is uploaded.
        if (Input::file($inputName)) {
            $image = $request->file($inputName);
            $imageExtension = $image->getClientOriginalExtension();
            $finalPath = preg_replace('/([^:])(\/{2,})/', '$1/',  $path . time() .'.'. $imageExtension);

            // Make image with Intervention image Manager.
            $image = $manager->make($image->getRealPath());

            // If size is set we will build and resize image.
            if (!empty($size)) {

                if (is_string($size) && $size !== '') {
                    // Explode size so we can resize it with Intervention Image.
                    $explodedSize = explode(',', trim($size));
                    if ($scale) {
                        // resize the image to a provided width  and constraint aspect ratio (auto height)
                        $image->resize($explodedSize[0], null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else {
                        $image->resize($explodedSize[0], $explodedSize[1]);
                    }
                    $image->save($finalPath);

                    // Set new parameter to request.
                    return $request[$inputName] = $path;

                } elseif (is_array($size) && !empty($size)) {

                    foreach ($size as $widthAndHeight) {
                        // Explode size so we can resize it with Intervention Image.
                        $explodedSize = explode(',', trim($widthAndHeight));

                        $imgName = time() .'-'. preg_replace('/\s+/', '', ($explodedSize[0]. '-' .$explodedSize[1])) .'.'. $imageExtension;
                        if (!empty($name)) {
                            $imgName = $name .'-'. preg_replace('/\s+/', '', ($explodedSize[0]. '-' .$explodedSize[1])) .'.'. $imageExtension;
                        }

                        $finalPath = preg_replace('/([^:])(\/{2,})/', '$1/', $path . $imgName);

                        if ($scale) {
                            // resize the image to a provided width  and constraint aspect ratio (auto height)
                            $image->resize($explodedSize[0], null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        } else {
                            $image->resize($explodedSize[0], $explodedSize[1]);
                        }
                        $image->save($finalPath);
                    }

                    // Set new parameter to request.
                    return $request[$inputName] = $imgName;
                }

            }
        }

        // Set empty string as input because there was not image uploaded.
        $request[$inputName] = '';
    }

    /**
     * Method isAbsolutePath check if provided path is absolute or relative.
     *
     * @param $path
     * @return bool | True if absolute otherwise False
     */
    private function isAbsolutePath($path)
    {
        if (!is_string($path)) {
            $mess = sprintf('String expected but was given %s', gettype($path));
            throw new \InvalidArgumentException($mess);
        }

        if (!ctype_print($path)) {
            $mess = 'Path can not have non-printable characters or be empty';
            throw new \DomainException($mess);
        }

        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:print:]]{2,}://)*)';

        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';

        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';
        $parts = [];
        if (!preg_match($regExp, $path, $parts)) {
            $mess = sprintf('Path is not valid, was given %s', $path);
            throw new \DomainException($mess);
        }

        if ($parts['root'] !== '') {
            return true;
        }

        return false;
    }
    
    /**
     *  Checks the response that came back after modifying a resource
     * 
     *  @param  mixed   $response
     *  @param  string  $action
     *  @param  string  $entity
     *  @param  mixed   $responceClass
     *  @return void
     *  @access public
     */
    public function handleModifyResponse($response, $action, $entity, $responceClass = null) 
    {
        $responceClassForComparrison = (!empty($responceClass)) ? $responceClass : 
            $this->model;
        
        if($response instanceof $responceClassForComparrison) {
            switch ($action) {
                case self::DELETE:
                    $this->flashSuccess(
                        trans('core.messages.status_delete', ['entity' => $entity])
                    );
                    return;
                case self::DESTROY:
                    $this->flashSuccess(
                        trans('core.messages.status_destroy', ['entity' => $entity])
                    );
                    return;
            }
            $this->flashSuccess(
                trans('core.messages.changed_status', ['entity' => $entity])
            );
        } else {
            $this->flashError(
                trans('core.messages.ooops')
            );
        }
        return;
    }
    
    /**
     *  Finds and retrieves a model instance with which the Controller is 
     *  working
     * 
     *  @param  integer $id
     *  @return App\Core\Model
     *  @access public
     */
    public function findEntityInstance($id) 
    {
        return call_user_func_array(
            $this->model .'::find', 
            [$id]
        );
    }

    /**
     * Method flashSuccess used to flash with success message UI.
     *
     * @param $message
     */
    protected function flashSuccess($message)
    {
        return request()->session()->flash(Flashbangs::MSG_SUCCESS, [(string) $message]);
    }

    /**
     * Method flashSuccess used to flash with error message UI.
     *
     * @param $message
     */
    protected function flashError($message)
    {
        return request()->session()->flash(Flashbangs::MSG_ERROR, [(string) $message]);
    }

    /**
     * Method flashSuccess used to flash with notice message UI.
     *
     * @param $message
     */
    protected function flashNotice($message)
    {
        return request()->session()->flash(Flashbangs::MSG_NOTICE, [(string) $message]);
    }    
    
    /**
     *  Redirects to the Users dashboard in accordance with his role and 
     *  flashes a message stating that he was denied access to a resource
     * 
     *  @return void
     *  @access protected
     */
    protected function redirectUnauthorizedUser() 
    {
        $this->flashError(trans('adminuser.messages.not_allowed'));
        return redirect()->route(auth()->user()->role .'-dashboard');
    }
}