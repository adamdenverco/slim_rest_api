<?php

class validate {

    protected $required_login_fields = ['username','password'];
    protected $required_user_fields = ['username', 'email', 'firstname', 'lastname', 'password'];
    protected $required_delete_fields = ['username','password'];

    // public function validate_login() {
    //     $output = [];
    //     return $output;
    // }

    public function validate_create_user_data($data) {
        $output = [];

        $this->echo_data_title_die($data);
        $this->echo_data_title_die($this->required_user_fields);

        if ( $output = $this->required_fields_are_set($data, $this->required_user_fields) ) { 
            # errors are returned to $output variable
        } elseif ( $output = $this->validate_username_email_and_password($data) ) {
            # errors are returned to $output variable
        }

        // $this->echo_data_title_die($output);
        // die('validate user data done');

        return $output;
    }

    public function validate_update_user_data($data) {
        // set default output
        $output = [];

        $this->echo_data_title_die($data);

        // validate user id is a valid integer
        if ( !$this->valid_integer_or_zero($data['id']) ) { 
            $output['status'] = 'error';
            $output['message'] = 'valid user id required';
        // may this user edit this user data?
        } elseif (!$this->permission_to_edit_user($data['id']) ) {
            $output['status'] = 'error';
            $output['message'] = 'you do not have permissions to update this user';
        // check that we have all fields
        } elseif ( $output = $this->required_fields_are_set($data, $this->required_user_fields) ) { 
            # then errors are returned to $output variable
        // check if username, email and password are all valid
        } elseif ( $output = $this->validate_username_email_and_password($data) ) {
            # then errors are returned to $output variable
        }

        // $this->echo_data_title_die($output);
        // die('validate user data done');
        return $output;
    }

    public function validate_username_email_and_password($data) {
        $output = [];
        // is the username alphanumeric?
        if (preg_match('/[^a-z0-9]/i', $data['username'])) {
            $output['status'] = 'error';
            $output['message'] = 'username may only contain letters and numbers';
        // do we have a valid email?
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $output['status'] = 'error';
            $output['message'] = 'email is invalid';
        // do we have a valid password?
        } elseif ( !$this->valid_password($data['password']) ) {
            $output['status'] = 'error';
            $output['message'] = 'password must contain one uppercase letter, one lowercase letter, one number and be 8 or more characters.';
        } elseif ( $this->username_or_email_assigned_to_another_user($data) ) {
            $output['status'] = 'error';
            $output['message'] = 'plase use a differnt username or email';
        }
        return $output;
    }

    public function validate_detele_user($data) {
        $output = [];
        if ( !(int)$args['id'] ) { // <---------- CHANGE
            $output['status'] = 'error';
            $output['message'] = 'valid user id required';
        }
        return $output;
    }

    public function required_fields_are_set($fields_data, $fields_required) {
        $output = [];
        $missing_fields = [];
        foreach ($fields_required as $key) {
            if ( $fields_data[$key] == '') {
                $missing_fields[] = $key;
                // $output = false;
                // break;
            }
        }
        if (count($missing_fields) > 0) {
            $output['status'] = 'error';
            $output['message'] = 'missing required fields: ' . str_replace('_', ' ', implode(', ', $missing_fields) );
        }
        return $output;
    }

    public function valid_password($password) {
        // must contain uppercase and lowercase characters
        // must contain a number
        // must be 8 or more characters
        return (
            preg_match('@[A-Z]@', $password) &&
            preg_match('@[a-z]@', $password) &&
            preg_match('@[0-9]@', $password) &&
            mb_strlen($password) >= 8
        ) ? true : false;
    }

    public function permission_to_edit_user($user_id) {
        $output = false;
        // token already required and checked by middleware
        // if user_id and token have values then check the user
        if ( $this->valid_integer_or_zero($user_id) && $GLOBALS['token']) {
            // see if there is a match for a user with this id and token
            $user = R::getRow(
                "select id, username, firstname, lastname, email, created, updated 
                from users where (id = :id) AND (status = 1) AND (token = :token) ", 
                array(':id' => $user_id, ':token' => $GLOBALS['token'] ) 
            );
            // if there is a match then they have permission to edit
            if ( count($user) > 0) {
                $output = true;
            }
        }
        return $output;
    }

    public function username_or_email_assigned_to_another_user($data) {
        $output = true;
        $user_id = $this->valid_integer_or_zero($data['id']);
        $username_or_email_in_use = R::getRow(
            "select id, username 
            from users where (id <> :id) AND ( (username = :username) OR (username = :email) )", 
            array(':id' => $user_id, ':username' => $data['username'], ':email' => $data['email'] ) 
        );
        if (count($username_or_email_in_use) == 0) {
            $output = false;
        }
        return $output;
    }

    public function valid_integer_or_zero($var) {
        return ( mb_strlen($var) == mb_strlen((int)$var) && (int)$var > 0) ? (int)$var : 0;
    }

    public function true_false($statement) {
        echo ( ($statement) ? 'true' : 'false' );
        return;
    }

    public function echo_data_title_die( $data = NULL, $title = NULL, $should_we_die = NULL) {
        if ($data || $title) {
            if (is_array($data) || is_object($data) ) {
                echo "<pre>";
                $this->echo_title($title);
                print_r($data);
                echo "</pre>\r\n";
            } else {
                $this->echo_title($title);
                echo $data . "<br/>\r\n";
            }
        }
        $this->echo_die($should_we_die);
        return;
    }


    private function echo_title( $title = NULL) {
        if ($title) { echo $title . ": \r\n"; }
        return;
    }

    private function echo_die( $should_we_die = NULL) {
        if ($should_we_die == ('yes' || 'y' || 'die' || 1) ) { 
            echo "we died.<br/>\r\n"; 
            die(); 
        }
        return;
    }

}