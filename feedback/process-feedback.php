<?php

    $feedbackType = $_POST['feedbackType'];
    $feedbackDetails = $_POST['feedbackDetails'];
    $feedbackResponse = ( $_POST['feedbackResponse'] == "yes" ) ? "Response requested" : "Response not requested";

    echo ( "<p>" . $feedbackType . "</p>" );
    echo ( "<p>" . $feedbackDetails . "</p>" );
    echo ( "<p>" . $feedbackResponse . "</p>" );
