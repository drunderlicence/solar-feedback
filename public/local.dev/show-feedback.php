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


    // Connect to mysql //

    $con = new mysqli( 'localhost', DB_USERNAME, DB_PASSWORD );
    if ( mysqli_connect_errno() )
    {
        Bail( 'Couldn\'t connect to mysql: ' . mysqli_connect_error() );
    }
    else
    {
        if ( VERBOSE_DEBUG )
        {
            echo '<p>Connected to mysql...</p>';
        }
    }


    // Select or create db //

    if ( !$con->select_db( DB_NAME ) )
    {
        if ( VERBOSE_DEBUG )
        {
            echo '<p>Creating db...</p>';
        }
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

    if ( VERBOSE_DEBUG )
    {
        echo '<p>Selected db...</p>';
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
            echo '<p>Creating table...</p>';
        }
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

    echo '<h3>Solar Feedback Form</h3>';

    echo '<p>Current Feedback</p>';


    // Get and print data //

    $result = $con->query( 'SELECT * FROM ' . TABLE_NAME );

    echo '<table>';

    while ( $row = $result->fetch_array() )
    {
        echo '<tr>';
        echo '<td>' . $row['school'] . '</td>';
        echo '<td>' . $row['user'] . '</td>';
        echo '<td>' . $row['type'] . '</td>';
        echo '<td>' . $row['content'] . '</td>';
        echo '<td>' . $row['response'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';

    $con->close();
