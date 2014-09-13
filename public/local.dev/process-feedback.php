<?php

    //ini_set('display_errors',1);
    //ini_set('display_startup_errors',1);
    //error_reporting(-1);

    // CONSTANTS //

    define ( 'DB_USERNAME', 'root' );
    define ( 'DB_PASSWORD', 'vagrant' );
    define ( 'DB_NAME', 'solar_feedback' ); // FIXME or whatever we like
    define ( 'TABLE_NAME', 'Feedback' );
    define ( 'ADMIN_EMAIL', 'test@test.com' ); // FIXME
    define ( 'VERBOSE_DEBUG', true );


    // FUNCTIONS //

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


    // VALIDATE DATA //

    $feedbackSchool = '<SCHOOL>'; // FIXME replace with real values
    $feedbackUser = '<USER>';     //
    $feedbackType = $_POST[ 'feedbackType' ];
    $feedbackDetails = $_POST[ 'feedbackDetails' ];
    $feedbackResponse = ( $_POST[ 'feedbackResponse' ] == 'on' ) ? true : false;

    if ( $feedbackType == null || $feedbackType == "" )
    {
        Bail( 'Feedback type not found in post' );
    }
    else if ( $feedbackDetails == null || $feedbackDetails == "" )
    {
        Bail( 'Feedback details not found in post' );
    }


    // CONNECT TO MYSQL //

    $con = new mysqli( 'localhost', DB_USERNAME, DB_PASSWORD );
    if ( mysqli_connect_errno() )
    {
        Bail( 'Couldn\'t connect to mysql: ' . mysqli_connect_error() );
    }
    else
    {
        DebugPrint( '<p>Connected to mysql...</p>' );
    }


    // SELECT OR CREATE DB //

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


    // CREATE TABLE IF NECESSARY //

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


    // INSERT DATA //

    $dbFeedbackSchool = $con->real_escape_string( $feedbackSchool );
    $dbFeedbackUser = $con->real_escape_string( $feedbackUser );
    $dbFeedbackType = $con->real_escape_string( $feedbackType );
    $dbFeedbackDetails = $con->real_escape_string( $feedbackDetails );
    $dbFeedbackResponse = ( $feedbackResponse ) ? 1 : 0;
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


    // SEND EMAIL //

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


    // SHOW CONFIRMATION //

    include 'feedback-received.html'; // FIXME there are many ways we can do this, change to be consistent with rest of site

    /* FIXME an alternative method
    echo '<h3>Thank you for your feedback</h3>';

    if ( $dbFeedbackResponse == 1 )
    {
        echo '<p>We will get back to you soon</p>';
    }
     */

    $con->close();
