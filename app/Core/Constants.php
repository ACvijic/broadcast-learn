<?php

namespace App\Core;

/**
 * Interface Constants used as global accessor for constants through application logic.
 *
 * @package App\Core
 */
interface Constants
{
    const STATUS_VISIBLE    = 1;
    const STATUS_HIDDEN     = 0;
    const DELETED           = 1;
    const NOT_DELETED       = 0;

    const USER_BANNED       = 0;
    const USER_ACTIVE       = 1;
    
    const DELETE            = 'delete';
    const CREATE            = 'create';
    const UPDATE            = 'update';
    const SHOW              = 'show';
    const HIDE              = 'hide';
    const MODIFY            = 'modify';
    const DESTROY           = 'destroy';
}