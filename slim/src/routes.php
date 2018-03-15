<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

/*
 * ---------------------------------------------------------------
 * DEFAULT ROUTE
 * ---------------------------------------------------------------
 */

// $app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");

//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });

/*
 * ---------------------------------------------------------------
 * CUSTOM ROUTES
 * ---------------------------------------------------------------
 */

$app->get('/', function (Request $request, Response $response, array $args) {
    // $app->response->setStatus(200);
    $response->getBody()->write(' Welcome to Slim based API ');
    return $response;
});

$app->post('/login', function (Request $request, Response $response, array $args) {

    // set default output
    $output = [
        'status' => '',
        'message' => '', 
        'data' =>  []
    ];

    // get login post data
    $post_data = $request->getParsedBody();

    // make sure we've got a posted username and password
    if ( !$post_data['username'] || !$post_data['password']) {

        // no username or password posted
        // so return an error
        $output['status'] = 'error';
        $output['message'] = 'Both username and password are required to login.';

    } else {

        $user = R::getRow(
            "select id, username, token, token_expire, firstname, lastname, email, created, updated 
            from users where (username = :username) AND (status = 1) AND (password = :password) ", 
            array(':username' => trim($post_data['username']), ':password' => md5(trim($post_data['password'])) ) 
        );

        // if no user data found
        if ( count($user) == 0 ) { 
            $output['status'] = 'error';
            $output['message'] = 'The username or password you entered is incorrect.';
        
        // else we found a valid user
        } else {

            // load user bean, generate token, set expiration and update user
            $update_user = R::load('users', $user['id']);
            $update_user->token = bin2hex(openssl_random_pseudo_bytes(8));
            $update_user->token_expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
            R::store($update_user);

            $output['status'] = 'success';
            $output['message'] = 'login successful!';
            $output['data'] = [
                // 'username' => $update_user->username,
                'token' => $update_user->token
            ];

        }

    }

    // $myclass = new myclass();
    // $response->getBody()->write( json_encode('test:'. $myclass->get_testvar() ) );

    $response->getBody()->write( json_encode( $output ) );
    return $response;

});

$app->get('/user[/{id}]', function (Request $request, Response $response, array $args) {

    // set default output
    $output = [
        'status' => '',
        'message' => '', 
        'data' =>  []
    ];

    if ( !(int)$args['id'] ) {

        $output['status'] = 'error';
        $output['message'] = 'valid user id required';

    } else {

        $user_id = (int)$args['id'];
        $tokenAuth = isset($request->getHeader('token')[0]) ? $request->getHeader('token')[0] : '';

        $user = R::getRow(
            "select id, username, token, token_expire, firstname, lastname, email, created, updated 
            from users where (id = :id) AND (status = 1) AND (token = :token) ", 
            array(':id' => $user_id, ':token' => $tokenAuth ) 
        );

        if ( !count($user) ) {
            $output['status'] = 'error';
            $output['message'] = 'you do not have permissions to retrieve this user';
        } else {
            $output['status'] = 'success';
            $output['data'] = $user;
        }
    
    }

    $response->getBody()->write( json_encode( $output ) );
    return $response;

})->add($auth_user_middleware);

// $app->post('/user[/{id}]', function (Request $request, Response $response, array $args) {

//     // set default output
//     $output = [
//         'status' => '',
//         'message' => '', 
//         'data' =>  []
//     ];

//     $post_data = $request->getParsedBody();

//     echo "<pre>";
//     print_r($post_data);
//     echo "</pre>";

//     try {
//         $user = R::dispense( 'users' );
//         $user->username = 'dudesmith';
//         $user->email = 'dude@gmail.com';
//         $user->password = 'temppass';
//         $user->firstname = 'dude';
//         $user->lastname = 'smith';
//         $user->created = date("Y-m-d H:i:s");
//         $user->updated = date("Y-m-d H:i:s");
//         $id = R::store( $user );
//     } catch (Exception $e) {
//         echo 'Caught exception: ',  $e->getMessage(), "\n";
//     }


//     echo "post";
//     die();
// })->add($auth_user_middleware);


$app->put('/user[/{id}]', function (Request $request, Response $response, array $args) {

    // set default output
    $output = [
        'status' => '',
        'message' => '', 
        'data' =>  []
    ];

    if ( !(int)$args['id'] ) {

        $output['status'] = 'error';
        $output['message'] = 'valid user id required';

    } else {

        $user_id = (int)$args['id'];
        $tokenAuth = isset($request->getHeader('token')[0]) ? $request->getHeader('token')[0] : '';

        $user = R::getRow(
            "select id, username, token, token_expire, firstname, lastname, email, created, updated 
            from users where (id = :id) AND (status = 1) AND (token = :token) ", 
            array(':id' => $user_id, ':token' => $tokenAuth ) 
        );

        if ( !count($user) ) {
            $output['status'] = 'error';
            $output['message'] = 'you do not have permissions to update this user';
        } else {
            // $output['status'] = 'success';
            // $output['data'] = $user;

            $put_data = $request->getParsedBody();

            // make sure we have all the fields
            if ( 
                !$put_data['username'] || !$put_data['email'] || 
                !$put_data['firstname'] || !$put_data['lastname'] || 
                !$put_data['password'] 
            ) {
                $output['status'] = 'error';
                $output['message'] = 'missing required fields';
            
            // make sure the email is valid
            } elseif (!filter_var($put_data['email'], FILTER_VALIDATE_EMAIL)) {
                $output['status'] = 'error';
                $output['message'] = 'email is invalid';
            
            // make sure username is alphanumeric
            } elseif (preg_match('/[^a-z0-9]/i', $put_data['username'])) {
                $output['status'] = 'error';
                $output['message'] = 'username must be alphanumeric';

            } else {

                $username_in_use = R::getRow(
                    "select id, username 
                    from users where (id <> :id) AND (status = 1) AND (username = :username) ", 
                    array(':id' => $user_id, ':username' => $put_data['username'] ) 
                );

                $email_in_use = R::getRow(
                    "select id, email 
                    from users where (id <> :id) AND (status = 1) AND (email = :email) ", 
                    array(':id' => $user_id, ':email' => $put_data['email'] ) 
                );

                // test if username is in use by another user
                if ( count($username_in_use) > 0 ) {
                    $output['status'] = 'error';
                    $output['message'] = 'username already in use by another user';

                // test if email is in use by another user
                } elseif ( count($email_in_use) > 0 ) {
                    $output['status'] = 'error';
                    $output['message'] = 'email already in use by another user';

                } else {

                    echo "<pre>";
                    print_r($put_data);
                    echo "</pre>";

                    // try to update the user
                    try {
                        $user_update = R::load( 'users', $user_id ); //reloads our book
                        $user_update->username = $put_data['username'];
                        $user_update->email = $put_data['email'];
                        $user_update->password = $put_data['password'];
                        $user_update->firstname = $put_data['firstname'];
                        $user_update->lastname = $put_data['lastname'];
                        $user_update->updated = date("Y-m-d H:i:s");
                        R::store( $user_update );

                    } catch (Exception $e) {
                        $output['status'] = 'error';
                        $output['message'] = $e->getMessage();
                    }

                    // if no error messages
                    if ( !$e->getMessage() ) {
                        $output['status'] = 'success';
                        $output['data'] = $user_update;

                    }

                }

            }
    
        




        }
    
    }

    $response->getBody()->write( json_encode( $output ) );
    return $response;

})->add($auth_user_middleware);

// $app->delete('/user[/{id}]', function (Request $request, Response $response, array $args) {

//     // set default output
//     $output = [
//         'status' => '',
//         'message' => '', 
//         'data' =>  []
//     ];

//     $delete_data = $request->getParsedBody();

//     echo "<pre>";
//     print_r($delete_data);
//     echo "</pre>";

//     echo "delete";
// })->add($auth_user_middleware);
