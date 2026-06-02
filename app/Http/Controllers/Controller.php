<?php

namespace App\Http\Controllers;

use App\Contracts\Action;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function exception(Exception $exception)
    {
        return back()->withErrors(__($exception->getMessage()))->withInput();
    }

    public function executeAction(Action $action)
    {
        try {
            return $action->execute();
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }
}
