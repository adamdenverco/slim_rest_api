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

    // $response->getBody()->write('This is a SLIM REST API. ');
    
    return $this->renderer->render($response, 'index.phtml', $args);

    // return $response;
});

// POST LOGIN: login and get token
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

        // echo "<pre>";
        // print_r($post_data);
        // echo "</pre>";

        $user = R::getRow(
            "select id, username, firstname, lastname, email, created, updated 
            from users where (username = :username) AND (status = 1) AND (password = :password) ", 
            array(':username' => trim($post_data['username']), ':password' => md5(trim($post_data['password'])) ) 
        );

        // if no user data found
        if ( count($user) == 0 ) { 
            $output['status'] = 'error';
            $output['message'] = 'The username or password you entered is incorrect.';
        
        // else we found a valid user
        } else {

            // generate token
            $GLOBALS['token'] = bin2hex(openssl_random_pseudo_bytes(8));

            // load user bean, generate token, set expiration and update user
            $update_user = R::load('users', $user['id']);
            $update_user->token = $GLOBALS['token'];
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

    $response->getBody()->write( json_encode( $output ) );
    return $response;

});

// POST USER: CREATE NEW USER
$app->post('/user[/{id}]', function (Request $request, Response $response, array $args) {

    // set default output
    $output = [
        'status' => '',
        'message' => '', 
        'data' =>  []
    ];

    // trim all post array variables
    $create_data = array_map('trim',$request->getParsedBody());

    // call up the validate object
    $validate = new validate();

    if ( $output = $validate->validate_create_user_data($create_data) ) {
        // catch any errors before allowing to create a new user
    } else {

        // our data is validated
        // including checking the uniqueness of the email and username

        $validate->echo_data_title_die($create_data, 'validated new user info');

        // try to create the user
        try {

            $user_create = R::dispense( 'users' );
            $user_create->username = $create_data['username'];
            $user_create->email = $create_data['email'];
            $user_create->password = md5($create_data['password']);
            $user_create->firstname = $create_data['firstname'];
            $user_create->lastname = $create_data['lastname'];
            $user_create->lastname = '1';
            $user_create->created = date("Y-m-d H:i:s");
            $user_create->updated = date("Y-m-d H:i:s");
            R::store( $user_create );

        } catch (Exception $e) {
            $output['status'] = 'error';
            $output['message'] = $e->getMessage();
        }

        // if no error messages
        if ( $output['status'] != 'error' ) {
            $output['status'] = 'success';
            $output['data'] = 'new account created. you may now log in.';
        }

    }

    $response->getBody()->write( json_encode( $output ) );
    return $response;

});

// PUT USER: UPDATE EXISTING USER
$app->get('/user', function (Request $request, Response $response, array $args) {

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
        $tokenAuth = $GLOBALS['token'];

        $user = R::getRow(
            "select id, username, firstname, lastname, email, created, updated 
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

$app->put('/user[/{id}]', function (Request $request, Response $response, array $args) {

    // set default output
    $output = [
        'status' => '',
        'message' => '', 
        'data' =>  []
    ];

    // trim all put array variables and add "id" from URL into the array
    $update_data = array_merge( array_map('trim',$request->getParsedBody()), [ 'id' => (int)$args['id'] ] );

    // call up the validate object
    $validate = new validate();

    if ( $output = $validate->validate_update_user_data($update_data) ) {
        # catch any errors before allowing to update user data
    } else {

        // try to update the user
        try {
            $user_update = R::load( 'users', $update_data[id] ); //reloads our book
            $user_update->username = $update_data['username'];
            $user_update->email = $update_data['email'];
            $user_update->password = md5($update_data['password']);
            $user_update->firstname = $update_data['firstname'];
            $user_update->lastname = $update_data['lastname'];
            $user_update->updated = date("Y-m-d H:i:s");
            R::store( $user_update );

        } catch (Exception $e) {
            $output['status'] = 'error';
            $output['message'] = $e->getMessage();
        }

        // if no error messages
        if ( $output['status'] != 'error' ) {
            $output['status'] = 'success';
            $output['data'] = $user_update;

        }

    }

    $response->getBody()->write( json_encode( $output ) );
    return $response;

})->add($auth_user_middleware);

$app->delete('/user[/{id}]', function (Request $request, Response $response, array $args) {

    // set default output
    $output = [];

    $delete_data = $request->getParsedBody();

    echo "<pre>";
    print_r($delete_data);
    echo "</pre>";

    // call up the validate object
    $validate = new validate();

    if ( $output = $validate->validate_delete_user_data($delete_data) ) {
        # catch any errors before allowing to update user data
    } else {

        // try to update the user
        try {
            $user_delete = R::load( 'users', $update_data[id] ); //reloads our book
            $user_delete->status = 2;
            $user_delete->token_expire = date('Y-m-d H:i:s', strtotime('-1 seconds'));
            $user_delete->updated = date("Y-m-d H:i:s");
            R::store( $user_delete );

        } catch (Exception $e) {
            $output['status'] = 'error';
            $output['message'] = $e->getMessage();
        }

        // if no error messages
        if ( $output['status'] != 'error' ) {
            $output['status'] = 'success';
            $output['data'] = 'account deleted.';
        }

    }

    $response->getBody()->write( json_encode( $output ) );
    return $response;

})->add($auth_user_middleware);
