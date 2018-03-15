<?php

// Application middleware
$auth_user_middleware = function ($request, $response, $next) {

    // get token from 
    $tokenAuth = isset($request->getHeader('token')[0]) ? $request->getHeader('token')[0] : '';
    // $tokenAuth = "";

    // if there is no token, then deny access
    if ( !$tokenAuth ) {

        $output = [
            'status' => 'error',
            'message' => 'token required', 
        ];
        echo json_encode( $output );
        http_response_code(401);
        exit;
    
    } else {

        $user = R::getRow(
            "select id, username, token, token_expire, firstname, lastname, email, created, updated 
            from users where (token = :token) AND (status = 1) AND (token_expire >= :token_expire) ", 
            array(':token' => $tokenAuth, ':token_expire' => date('Y-m-d H:i:s') ) 
        );

        // echo "<pre>";
        // print_r($user);
        // echo "</pre>";

        // if no user is found for the token, or the token is expired 
        // or the user is inactive, then deny access
        if ( count($user) == 0 ) { 

            $output = [
                'status' => 'error',
                'message' => 'invalid or expired token', 
            ];
            echo json_encode( $output );
            http_response_code(401);
            exit;
    
        // else we found an active user with a non-expired token
        } else {

            // update the expiration date for the token so it persists
            $update_user = R::load('users', $user['id']);
            $update_user->token_expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
            R::store($update_user);

        }

    }

    // $response->getBody()->write('BEFORE');
    $response = $next($request, $response);
    // $response->getBody()->write('AFTER');
    return $response;
};
