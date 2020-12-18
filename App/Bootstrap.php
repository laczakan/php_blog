<?php

namespace App;

use App\Controllers\NotFoundController;

/**
 * Base class which index.php is calling.
 *
 * @category Boot
 * @package  App
 */
class Bootstrap
{
    /**
     * Suffix added after the controller name
     *
     * @var string Suffix added after the controller name
     */
    private $controllerSuffix = 'Controller';

    /**
     * Suffix added after the action name
     *
     * @var string Suffix added after the action name
     */
    private $actionSuffix = 'Action';

    /**
     * Run application.
     *
     * @return void
     */
    public function run(): void
    {
        // After session_start we will have $_SESSION global variable
        session_start();

        // Require app config where we define URL constants
        include_once 'config/app.php';

        // Require database config where we define database constants
        include_once 'config/database.php';

        // Require helpers from helpers folder
        include_once 'helpers/helpers.php';

        // 1 of 4: Split an url string by / character to an array
        $router = explode('/', $_GET['url']);

        // 2 of 4: First element in the url it's controller name
        // 2 of 4: If URL is just /, then set controller name to `index`
        $controller = $router[0] ?: 'index';

        // 3 of 4: Second element is an action name, `index` if not provided
        $action = $router[1] ?? 'index';

        // 4 of 4: Third+ element it's parameter, it can be ID or status e.g. /articles/list/deleted
        $params = array_slice($router, 2);

        /**
        * Prepare controller name:
        * 1. Add prefix (App\Controllers namespace) on the beginning.
        * 2. Make a string's first character uppercase e.g. articles => Articles
        * 3. Add Controller suffix
        */
        $controllerName = '\\App\\Controllers\\' . ucfirst($controller) . $this->controllerSuffix;

        // Prepare action name, add acction suffix e.g details => detailsAction
        $actionName = $action . $this->actionSuffix;
        $continue = true;

        if (class_exists($controllerName)) {
            // Instantiate the right controller
            $class = new $controllerName();

            if (method_exists($class, $actionName)) {
                // Set view which we will require in the template
                $class->setView($controller . '/' . $action);

                // Sent parameters to controller
                $class->setParams($params);
                
                // Call an acction and pass a parameter as an argument
                $return = $class->$actionName();

                if ($return === false) {
                    $continue = false;
                }
            } else {
                $class = new NotFoundController();
                $class->setView('notFound/action');
                $class->indexAction();
            }
        } else {
            $class = new NotFoundController();
            $class->setView('notFound/controller');
            $class->indexAction();
        }

        if ($continue) {
            $class->afterAction();
        }
    }
}
