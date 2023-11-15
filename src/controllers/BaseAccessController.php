<?php

namespace dukt\analytics\controllers;

use craft\web\Controller;
use dukt\analytics\base\RequireAccessTrait;

abstract class BaseAccessController extends Controller
{
    use RequireAccessTrait;

    protected bool $requirePluginAccess = false;

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // All actions require control panel requests

        if($this->requirePluginAccess) {
            $this->requirePluginAccess();
        }

        return true;
    }
}