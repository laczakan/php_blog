<?php

/**
 * Redirect user to a different page.
 *
 * @param string $page Url to redirect, home page if empty
 *
 * @return bool
 */
function redirect($page = null)
{
    // add location header which redirect to new location.
    header('Location: ' . ROOT_URL . BASE_URL . $page);
    return false;
}

/**
 *  Dump variables using var_dump and die.
 *  func_get_args() Returns an array comprising a function's argument list, 3 dots changes for arguments.
 *
 * @return void
 */
function vd()
{
    var_dump(...func_get_args());
    die;
}

/**
 * Dump variables using ice dump() function
 *
 * @return void
 */
function vdi()
{
    $dump = new Ice\Dump(true);
    echo $dump->vars(...func_get_args());
}

/**
 * Load view($name) if exist.
 *
 * @param string $name      name of view to load
 * @param array  $variables variables which will be sent to the view
 *
 * @return void
 * phpcs:disable
 */
function load_view($name, $variables = [])
{
    // Convert keys from the array to the variables with the same name
    extract($variables);

    if (file_exists(__DIR__ . '/../Views/' . $name . '.php')) {
        include __DIR__ . '/../Views/' . $name . '.php';
    }
}

// phpcs:enable

/**
 * Get URL string
 *
 * @param string $uri  link to be generated
 * @param bool   $root add ROOT_URL if true
 *
 * @return bool
 */
function url(string $uri, $root = false)
{
    // if $root true then add ROOT_URL else empty string,
    // concatenate BASE_URL and passed $uri string
    return ($root ? ROOT_URL : '') . BASE_URL . $uri;
}

/**
 * Store alert in session
 *
 * @param $type    set type of message
 * @param $message custom message
 *
 * @return void
 * phpcs:disable
 */
function set_alert($type, $message)
{
    //add alert to session
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message,
    ];
}

// phpcs:enable

/**
 * Check if there is allert in session
 *
 * @return bool
 * phpcs:disable
 */
function has_alert()
{
    return isset($_SESSION['alert']);
}

// phpcs:enable

/**
 * Get alert from session and remove it
 *
 * @return bool
 * phpcs:disable
 */
function get_alert()
{
    if (!has_alert()) {
        return null;
    }

    $alert = $_SESSION['alert'];

    //bootstrap 4 alert - sent to the view as optput
    $output = '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">' .
        $alert['message'] .
        '<button type="button" class="close" data-dismiss="alert">' .
            '<span>&times;</span>' .
        '</button>' .
    '</div>';

    //remove alert from session
    unset($_SESSION['alert']);

    return $output;
}
// phpcs:enable
