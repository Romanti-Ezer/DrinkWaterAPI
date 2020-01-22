<?php

require_once('response.php');

class Rest
{
    protected $server;
    public function handleRequest($request, $server)
    {
        $this->server = $server;
        if ($this->server['REQUEST_METHOD'] != 'GET' && $this->server['REQUEST_METHOD'] != 'DELETE' && (!isset($this->server['CONTENT_TYPE']) || $this->server['CONTENT_TYPE'] != 'application/json'))
        {
            $message = isset($this->server['CONTENT_TYPE']) ? 'Invalid content-type: ' . $this->server['CONTENT_TYPE'] . '. application/json expected.' : 'Invalid content-type. application/json expected.';
            $this->outputResponse(Response::getMessage(HTTP_BAD_REQUEST), $message, "Error");
        }
        $url = $request['url'];

        // Clear '/' (slash) in the end
        if(substr($url, -1) == '/')
            $url = substr($url, 0, -1);

        $this->handleRoute($url);
    }

    public function handleRoute($url)
    {
        $routeExists = false;
        foreach(ROUTES as $route)
        {
            // If route exists
            // If it's the expected request
            if( preg_match($route['path'], $url) && $route['requestMethod'] == $this->server['REQUEST_METHOD'])
            {
                $class = $route['class'];
                $method = $route['method'];
                $routeExists = true;
                break;
            }        
        }

        if (!$routeExists)
        {
            $this->outputResponse(Response::getMessage(HTTP_NOT_FOUND), 'Invalid route.');
        }

        $content = json_decode(trim(file_get_contents("php://input")));
        if (!(json_last_error() == JSON_ERROR_NONE) && $content && !is_array($content))
            $this->outputResponse(Response::getMessage(HTTP_UNPROCESSABLE_ENTITY), 'Invalid JSON.', 'Error');;
        $neededInstace = new $class;
        $neededInstace->$method($content);
    }

    public static function outputResponse($httpResponse, $data, $status = 'Success')
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $httpResponse);
        echo json_encode(array('status' => $status, 'data' => $data));
        die();
    }
}