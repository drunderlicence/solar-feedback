<?php

    // CONSTANTS //

    define ( "DB_USERNAME", "root" );
    define ( "DB_PASSWORD", "vagrant" );
    define ( "DB_NAME", "solar_feedback" ); // FIXME or whatever we like
    define ( "TABLE_NAME", "Feedback" );

    define ( "VERBOSE_DEBUG", false );

    function Bail( $error )
    {
        echo "<h3>Couldn't submit feedback</h3>";
        if ( VERBOSE_DEBUG )
        {
            echo "<p>$error</p>";
        }
        else
        {
            echo "<p>Please contact XXX with details of the situtation</p>";
        }
        exit(1);
    }


    // Connect to mysql //

    $con = new mysqli( 'localhost', DB_USERNAME, DB_PASSWORD );
    if ( mysqli_connect_errno() )
    {
        Bail( "Couldn't connect to mysql: " . mysqli_connect_error() );
    }
    else
    {
        if ( VERBOSE_DEBUG )
        {
            echo "<p>Connected to mysql...</p>";
        }
    }


    // Select or create db //

    if ( !$con->select_db( DB_NAME ) )
    {
        if ( VERBOSE_DEBUG )
        {
            echo "<p>Creating db...</p>";
        }
        $sql = 'CREATE DATABASE ' . DB_NAME;
        if ( $con->query( $sql ) )
        {
            if ( !$con->select_db( DB_NAME ) ) // select newly created db
            {
                Bail( "Couldn't select db after creating?? " . $con->error );
            }
        }
        else
        {
            Bail( "Couldn't create db: " . $con->error );
        }
    }

    if ( VERBOSE_DEBUG )
    {
        echo "<p>Selected db...</p>";
    }

    // Create table if necessary //

    function TableExists( $con, $tableName )
    {
        return $con->query( "SELECT 1 FROM `$tableName`" );
    }

    if ( !TableExists( $con, TABLE_NAME ) )
    {
        if ( VERBOSE_DEBUG )
        {
            echo "<p>Creating table...</p>";
        }
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
        if ( !$con->query( $sql ) )
        {
            Bail( "Couldn't create table: " . $con->error );
        }
    }


    // Insert data //

    $dbFeedbackType = $con->real_escape_string( $_POST[ 'feedbackType' ] );
    $dbFeedbackDetails = $con->real_escape_string( $_POST[ 'feedbackDetails' ] );
    $dbFeedbackResponse = ( $_POST[ 'feedbackResponse' ] == $con->real_escape_string( "on" ) ) ? 1 : 0;
    $sql = "INSERT INTO `" . TABLE_NAME . "` (school, user, type, content, response)
        VALUES ('<SCHOOL>', '<USER>', '$dbFeedbackType', '$dbFeedbackDetails', '$dbFeedbackResponse')";
    if ( !$con->query( $sql ) )
    {
        Bail( "Insert row failed: " . $con->error );
    }
    else
    {
        if ( VERBOSE_DEBUG )
        {
            echo "<p>Inserting row...</p>";
        }
    }


    // Show confirmation //
    // FIXME new page?

    echo "<h3>Thank you for your feedback</h3>";

    if ( $dbFeedbackResponse == 1 )
    {
        echo "<p>We will get back to you soon</p>";
    }

    $con->close();
