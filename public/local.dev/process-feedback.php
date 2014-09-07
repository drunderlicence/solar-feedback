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
