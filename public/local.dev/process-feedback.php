<?php

    echo( "<h3>_POSTs</h3>" );

    foreach ( $_POST as $key => $value )
    {
        echo $key . ' = ' . $value . '<br />';
    }

    echo( "<hr />" );

    $feedbackType = $_POST['feedbackType'];
    $feedbackDetails = $_POST['feedbackDetails'];
    $feedbackResponse = ( $_POST['feedbackResponse'] == "on" ) ? "Response requested" : "Response not requested";

    echo ( "<p>" . $feedbackType . "</p>" );
    echo ( "<p>" . $feedbackDetails . "</p>" );
    echo ( "<p>" . $feedbackResponse . "</p>" );

    // Connect to mysql
    define ( "DB_USERNAME", "root" );
    define ( "DB_PASSWORD", "vagrant" );
    $con = new mysqli( 'localhost', DB_USERNAME, DB_PASSWORD );
    if ( mysqli_connect_errno() )
    {
        die ( "Couldn't connect to mysql: " . mysqli_connect_error() );
    }

    // Get db
    define ( "DB_NAME", "solar_feedback" ); // FIXME or whatever
    $db = $con->select_db( DB_NAME );

    if ( !$db )
    {
        // Assume db doesn't exist and create it
        $sql = 'CREATE DATABASE ' . DB_NAME;

        if ( $con->query( $sql ) )
        {
            echo "Created db " . DB_NAME;
            $db = $con->select_db( DB_NAME );
            if ( !$db )
            {
                die ( "Couldnt select db after creating?? " . $con->error );
            }
        }
        else
        {
            die ( "Couldn't create db: " . $con->error );
        }
    }

    // Create table if necessary
    define ( "TABLE_NAME", "Feedback" );
    if ( $con->query( "SELECT 1 FROM `" . TABLE_NAME . "`" ) == FALSE )
    {
        $sql = "CREATE TABLE `" . TABLE_NAME . "`
            (
                feedback_id INT NOT NULL AUTO_INCREMENT,
                PRIMARY KEY(feedback_id),
                school VARCHAR(50),
                user VARCHAR(50),
                type VARCHAR(15),
                content TEXT,
                response BOOL
            )";
        if ( $con->query( $sql ) )
        {
            echo ( "Made table" );
        }
        else
        {
            echo "Table query failed: " . $con->error;
        }
    }
    else
    {
        echo ( "Did not make table" );
    }

    // Insert data
    $dbFeedbackType = $con->real_escape_string( $_POST[ 'feedbackType' ] );
    $dbFeedbackDetails = $con->real_escape_string( $_POST[ 'feedbackDetails' ] );
    $dbFeedbackResponse = ( $_POST[ 'feedbackResponse' ] == "on" ) ? 1 : 0;
    $sql = "INSERT INTO `" . TABLE_NAME . "` (school, user, type, content, response)
        VALUES ('<SCHOOL>', '<USER>', '$dbFeedbackType', '$dbFeedbackDetails', '$dbFeedbackResponse')";
    if ( $con->query( $sql ) )
    {
        echo "added row";
    }
    else
    {
        echo "Row failed: " . $con->error;
    }

    $con->close();
