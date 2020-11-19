<?php

namespace App\Controllers;

/**
 * Base controller which is used by all the child controllers.
 *
 * @category Controller
 * @package  App
 */
class Controller
{
    /**
     * Template name to load
     *
     * @var string
     */
    protected $template = 'template';

    /**
     * Parameters from url
     *
     * @var array
     */
    protected $params = [];

    /**
     * Variables to send to the view
     *
     * @var array
     */
    protected $variables = [];

    /**
     * View name to load
     *
     * @var string
     */
    protected $view;

    /**
     * Set Parameters from url to Controller
     *
     * @param array $params parameters from url
     *
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Get 1 (one) parameter from URL to Controllers
     *
     * @param int $key parameter index (default 0-first)
     *
     * @return string default null if not found.
     */
    public function getParam(int $key = 0)
    {
        return $this->params[$key] ?? null;
    }

    /**
     * Set view which we will load in the template.
     *
     * @param string $view View name
     *
     * @return void
     */
    public function setView(string $view)
    {
        $this->view = $view;
    }

    /**
     * AfterAction, send variables to the view, load template and view.
     *
     * @return void
     */
    public function afterAction()
    {
        // Add view as a variable available in the all views
        // by default all views are in helpers.
        $this->variables['view'] =  $this->view;

        // Make other variables accessable in the view
        $variables = $this->variables;
        
        // load template and pass variables as a second parameter
        load_view($this->template, $variables);
    }
}
