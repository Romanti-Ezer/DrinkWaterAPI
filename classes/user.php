<?php

require_once('classes/database.php');

class User
{
    protected $connection;
    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    // POST
    // /users
    public function store($data)
    {
        $this->validateData($data);

        $password = md5($data->password);
        $token = bin2hex(random_bytes(32));

        try {
            // Verify if e-mail is being used
            $query = "SELECT id, email FROM user WHERE email=:email";
            $query = $this->connection->prepare($query);
            $query->bindParam(':email', $data->email, PDO::PARAM_STR);
            $query->execute();

            $count = $query->rowCount();

            if($count > 0)
            {
                Rest::outputResponse(Response::getMessage(HTTP_CONFLICT), 'E-mail already in use.', 'Error');
            }

            // Create the user
            $query = "INSERT INTO user(name, email, password, token) VALUES(:name, :email, :password, :token)";
            $query = $this->connection->prepare($query);
            $query->bindParam(':name', $data->name, PDO::PARAM_STR);
            $query->bindParam(':email', $data->email, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->bindParam(':token', $token, PDO::PARAM_STR);
            $query->execute();
            $count = $query->rowCount();

            if($count > 0) {
                $user = array(
                    "name" => $data->name,
                    "email" => $data->email,
                    "drink_counter" => 0,
                    "token" => $token,
                );
                Rest::outputResponse(Response::getMessage(HTTP_CREATED), $user);
            } else {
                Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $this->connection->errorInfo(), "Error");
            }
        } catch(PDOExecption $e) {
            $query->rollback();
            Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
        }

        $query = null;
        $this->connection = null;
    }
    
    // POST
    // /login
    public function login($data)
    {
        $password = md5($data->password);

        try {
            $query = "SELECT name, email, drink_counter, token FROM user WHERE email = :email AND password = :password";
            $query = $this->connection->prepare($query);
            $query->bindParam(':email', $data->email, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->execute();

            $results = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC))
            {
                $results[] = $row;
            }

            if (count($results) > 0)
            {
                Rest::outputResponse(Response::getMessage(HTTP_OK), $results);
            } else {
                Rest::outputResponse(Response::getMessage(HTTP_UNAUTHORIZED), 'User doesn\'t exist or invalid password.', 'Failed');
            }
        } catch(PDOExecption $e) {
            $query->rollback();
            Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
        }
        $query = null;
        $this->connection = null;
    }

    // GET
    // /users/:id
    public function show($data)
    {
        $token = $this->getToken();
        $id = $this->getuserId();

        if ($id && $token)
        {
            try {
                $query = "SELECT id, name, email, drink_counter, token FROM user WHERE id = :id LIMIT 1";
                $query = $this->connection->prepare($query);
                $query->bindParam(':id', $id, PDO::PARAM_INT);
                $query->execute();

                $results = array();

                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                    $results[] = $row;
                }

                if (count($results) > 0)
                {
                    if( !($results[0]['token'] == $token) ) {
                        Rest::outputResponse(Response::getMessage(HTTP_FORBIDDEN), 'Unauthorized to access this user', 'Error');
                    }
                    // It cannot return the token
                    unset($results[0]['token']);
                    Rest::outputResponse(Response::getMessage(HTTP_OK), $results, 'Success');
                } else {
                    Rest::outputResponse(Response::getMessage(HTTP_NOT_FOUND), 'Id not found.', 'Error');
                }
            } catch(PDOExecption $e) {
                $query->rollback();
                Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
            }
        } else {
            Rest::outputResponse(Response::getMessage(HTTP_UNPROCESSABLE_ENTITY), 'Invalid Id or Token.', 'Error');
        }

        $query = null;
        $this->connection = null;
    }

    // GET
    // /users
    public function index($data)
    {
        $token = $this->getToken();

        if ($token)
        {
            try {
                $query = "SELECT id, name, email, drink_counter, token FROM user ORDER BY id ASC";
                $query = $this->connection->prepare($query);
                $query->bindParam(':token', $token, PDO::PARAM_STR);
                $query->execute();

                $results = array();

                $validToken = false;
                while($row = $query->fetch(PDO::FETCH_ASSOC))
                {
                    if ($row['token'] == $token)
                        $validToken = true;
                    // It cannot return the token
                    unset($row['token']);
                    $results[] = $row;
                }

                if (count($results) > 0)
                {
                    if(!$validToken) {
                        Rest::outputResponse(Response::getMessage(HTTP_FORBIDDEN), 'Invalid token', 'Error');
                    }

                    Rest::outputResponse(Response::getMessage(HTTP_OK), $results, 'Success');
                } else {
                    Rest::outputResponse(Response::getMessage(HTTP_NOT_FOUND), 'There are no users to show.', 'Error');
                }
            } catch(PDOExecption $e) {
                $query->rollback();
                Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
            }
        } else {
            Rest::outputResponse(Response::getMessage(HTTP_UNAUTHORIZED), 'User not authenticated.', 'Error');
        }

        $query = null;
        $this->connection = null;
    }

    // PUT
    // /users/:id/
    public function update($data)
    {
        $this->validateData($data);

        $password = md5($data->password);
        $token = $this->getToken();
        $id = $this->getuserId();

        if( $id && $token )
        {
            try {
                // Verify if e-mail is being used
                // Except himself
                $query = "SELECT id, email FROM user WHERE email=:email AND id != :id";
                $query = $this->connection->prepare($query);
                $query->bindParam(':email', $data->email, PDO::PARAM_STR);
                $query->bindParam(':id', $id, PDO::PARAM_INT);
                $query->execute();

                $count = $query->rowCount();

                if($count > 0) 
                {
                    Rest::outputResponse(Response::getMessage(HTTP_CONFLICT), 'E-mail already in use.', 'Error');
                }

                // Update the user
                $query = "UPDATE user set name = :name, email = :email, password = :password WHERE id=:id AND token = :token";
                $query = $this->connection->prepare($query);
                $query->bindParam(':name', $data->name, PDO::PARAM_STR);
                $query->bindParam(':email', $data->email, PDO::PARAM_STR);
                $query->bindParam(':password', $password, PDO::PARAM_STR);
                $query->bindParam(':id', $id, PDO::PARAM_INT);
                $query->bindParam(':token', $token, PDO::PARAM_STR);
                $query->execute();
                
                $count = $query->rowCount();

                if($count > 0) 
                {
                    Rest::outputResponse(Response::getMessage(HTTP_OK), array());
                } else {
                    Rest::outputResponse(Response::getMessage(HTTP_FORBIDDEN), 'Unauthorized, invalid id or no changes made.', 'Error');
                }
            } catch(PDOExecption $e) {
                $query->rollback();
                Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
            }
        } else {
            Rest::outputResponse(Response::getMessage(HTTP_UNPROCESSABLE_ENTITY), 'Invalid Id or Token.', 'Error');
        }

        $query = null;
        $this->connection = null;
    }

    // DELETE
    // /users/:id/
    public function destroy($data)
    {
        $token = $this->getToken();
        $id = $this->getuserId();

        if( $id && $token )
        {
            try {
                // Update the user
                $query = "DELETE FROM user WHERE id = :id AND token = :token";
                $query = $this->connection->prepare($query);
                $query->bindParam(':token', $token, PDO::PARAM_STR);
                $query->bindParam(':id', $id, PDO::PARAM_INT);
                $query->execute();
                $count = $query->rowCount();

                if($count > 0) {
                    Rest::outputResponse(Response::getMessage(HTTP_OK), array());
                } else {
                    Rest::outputResponse(Response::getMessage(HTTP_FORBIDDEN), 'Unauthorized or invalid id.', 'Error');
                }
            } catch(PDOExecption $e) {
                $query->rollback();
                Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
            }
        } else {
            Rest::outputResponse(Response::getMessage(HTTP_UNPROCESSABLE_ENTITY), 'Invalid id or token.', 'Error');
        }

        $query = null;
        $this->connection = null;
    }

    // POST
    // /users/:id/drink
    public function drink($data)
    {
        $token = $this->getToken();
        $id = $this->getuserId();
        $ml = isset($data->drink_ml) ? $data->drink_ml : null;
        $ml = $ml && is_numeric(intval($ml)) ? intval($ml) : null;

        if( $id && $token && $ml )
        {
            try {
                // Update the user
                $query = "UPDATE user set drink_counter = drink_counter + :ml WHERE id = :id AND token = :token";
                $query = $this->connection->prepare($query);
                $query->bindParam(':token', $token, PDO::PARAM_STR);
                $query->bindParam(':id', $id, PDO::PARAM_INT);
                $query->bindParam(':ml', $ml, PDO::PARAM_INT);
                $query->execute();
                $count = $query->rowCount();

                if($count > 0) {
                    $this->show($data);
                } else {
                    Rest::outputResponse(Response::getMessage(HTTP_FORBIDDEN), 'Unauthorized or invalid id.', 'Error');
                }
            } catch(PDOExecption $e) {
                $query->rollback();
                Rest::outputResponse(Response::getMessage(HTTP_INTERNAL_SERVER_ERROR), $e->getMessage(), "Error");
            }
        } else {
            Rest::outputResponse(Response::getMessage(HTTP_UNPROCESSABLE_ENTITY), 'Invalid id, token or drink_ml.', 'Error');
        }

        $query = null;
        $this->connection = null;
    }

    public function validateData($data) {
        $errors = array();
        if (!isset($data->name) || $data->name == '')
        {
            $errors[] = array(
                'name' => 'Required field.'
            );
        }
        if (!isset($data->email) || $data->email == '')
        {
            $errors[] = array(
                'email' => 'Required field.'
            );
        }
        if (isset($data->email) && !(filter_var($data->email, FILTER_VALIDATE_EMAIL))) {
            $errors[] = array(
                'email' => 'Invalid e-mail.'
            );
        }
        
        if (!isset($data->password) || $data->password == '')
        {
            $errors[] = array(
                'password' => 'Required field.'
            );
        }
        if (count($errors) > 0) {
            Rest::outputResponse(Response::getMessage(HTTP_UNPROCESSABLE_ENTITY), array('errors' => $errors), 'Error');
        }
    }

    public function getUserId() {
        $urlArray = explode('/', $_REQUEST['url']);
        // Clear empty values
        $urlArray = array_filter($urlArray, function($url)
        {
            return $url;
        });

        if (is_numeric(intval($urlArray[1])))
        {
            return intval($urlArray[1]);
        } else {
            return null;
        }
    }

    public function getToken() {
        $headers = getallheaders();
        if (isset($headers['token'])) 
        {
            return $headers['token'];
        } else {
            return null;
        }
    }
}