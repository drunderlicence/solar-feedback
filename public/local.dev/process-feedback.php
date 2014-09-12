<?php

    // CONSTANTS //

    define ( 'DB_USERNAME', 'root' );
    define ( 'DB_PASSWORD', 'vagrant' );
    define ( 'DB_NAME', 'solar_feedback' ); // FIXME or whatever we like
    define ( 'TABLE_NAME', 'Feedback' );
    define ( 'ADMIN_EMAIL', 'test@test.com' ); // FIXME
    define ( 'VERBOSE_DEBUG', true );

    function Bail( $error )
    {
        echo '<h3>Couldn\'t submit feedback</h3>';
        if ( VERBOSE_DEBUG )
        {
            echo "<p>$error</p>";
        }
        else
        {
            $admin = ADMIN_EMAIL;
            echo "<p>We are sorry, there are an error submitting your request. Please contact $admin for assistance</p>";
        }
        exit(1);
    }

    function DebugPrint( $message )
    {
        if ( VERBOSE_DEBUG )
        {
            echo "<p>$message</p>";
        }
    }


    // Connect to mysql //

    $con = new mysqli( 'localhost', DB_USERNAME, DB_PASSWORD );
    if ( mysqli_connect_errno() )
    {
        Bail( 'Couldn\'t connect to mysql: ' . mysqli_connect_error() );
    }
    else
    {
        DebugPrint( '<p>Connected to mysql...</p>' );
    }


    // Select or create db //

    if ( !$con->select_db( DB_NAME ) )
    {
        DebugPrint( '<p>Creating db...</p>' );
        $sql = 'CREATE DATABASE ' . DB_NAME;
        if ( $con->query( $sql ) )
        {
            if ( !$con->select_db( DB_NAME ) ) // select newly created db
            {
                Bail( 'Couldn\'t select db after creating?? ' . $con->error );
            }
        }
        else
        {
            Bail( 'Couldn\'t create db: ' . $con->error );
        }
    }

    DebugPrint( '<p>Selected db...</p>' );

    // Create table if necessary //

    function TableExists( $con, $tableName )
    {
        return $con->query( "SELECT 1 FROM `$tableName`" );
    }

    if ( !TableExists( $con, TABLE_NAME ) )
    {
        DebugPrint( '<p>Creating table...</p>' );
        $sql = 'CREATE TABLE `' . TABLE_NAME . '`
            (
                feedback_id INT NOT NULL AUTO_INCREMENT,
                PRIMARY KEY(feedback_id),
                school VARCHAR(50),
                user VARCHAR(50),
                type VARCHAR(15),
                content TEXT,
                response BOOL
            )';
        if ( !$con->query( $sql ) )
        {
            Bail( 'Couldn\'t create table: ' . $con->error );
        }
    }


    // Insert data //

    $dbFeedbackSchool = '<SCHOOL>';
    $dbFeedbackUser = '<USER>';
    $dbFeedbackType = $con->real_escape_string( $_POST[ 'feedbackType' ] );
    $dbFeedbackDetails = $con->real_escape_string( $_POST[ 'feedbackDetails' ] );
    $dbFeedbackResponse = ( $_POST[ 'feedbackResponse' ] == $con->real_escape_string( 'on' ) ) ? 1 : 0;
    $sql = "INSERT INTO `" . TABLE_NAME . "` (school, user, type, content, response)
        VALUES ('$dbFeedbackSchool', '$dbFeedbackUser', '$dbFeedbackType', '$dbFeedbackDetails', '$dbFeedbackResponse')";
    if ( !$con->query( $sql ) )
    {
        Bail( 'Insert row failed: ' . $con->error );
    }
    else
    {
        DebugPrint( '<p>Inserting row...</p>' );
    }


    // Send email //

    $to = ADMIN_EMAIL;
    $from = 'test@test.com'; // FIXME something like this
    $subject = 'Solar Feedback Form';

    $fd = $_POST[ 'feedbackDetails' ]; // Grab directly from post preserves newlines FIXME - safe?

    $rspv = ( $dbFeedbackResponse == 1 ) ? "\r\n\r\nThe user requested a response to this feedback." : "";
    $message =
<<<EEMMAAIILLSS
School: $dbFeedbackSchool
User: $dbFeedbackUser
Type: $dbFeedbackType
Content:

$fd

----$rspv

Love,
Solar Feedback System
EEMMAAIILLSS;

    $headers  = "To: Admin <$to>" . "\r\n";
    $headers .= "From: Solar Feedback <$from>" . "\r\n";

    if ( !mail( $to, $subject, $message, $headers ) )
    {
        Bail( 'Couldn\'t send notification email.' );
    }
    else
    {
        DebugPrint( '<p>Sending email...</p>' );
    }

    // Show confirmation //

    include 'feedback-received.html'; // FIXME there are many ways we can do this, change to be consistent with rest of site

    /* FIXME an alternative method
    echo '<h3>Thank you for your feedback</h3>';

    if ( $dbFeedbackResponse == 1 )
    {
        echo '<p>We will get back to you soon</p>';
    }
     */

    $con->close();
