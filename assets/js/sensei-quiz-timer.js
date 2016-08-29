jQuery(document).ready( function($) {
    var showAlert = true;
    var submitForm = true;

    // On click start timer and show quiz
    $('#start-quiz').click(function(e) {
        e.preventDefault();
        $('#timerbox').show();
        $.ajax({
            async: true,
            url: ajaxurl,
            data: {
                'action': 'quiz_start',
            },
            dataType: "json",
            success:function() {
                // This outputs the result of the ajax request
                $('#start-quiz').hide();
                $('#quiz-form').show();
                // Run update of timer every second
                setInterval(function(){
                    loadTimer() // this will run after every second
                }, 1000);
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });


    });
    // Load the timer info ito the div so the user can keep track
    function loadTimer(){
        $.ajax({
            async: true,
            url: ajaxurl,
            data: {
                'action': 'quiz_time',
            },
            dataType: "html",
            success:function(data) {
                //alert(data);
                // This outputs the result of the ajax request
                $('#timerbox').html(data);
                worker();

            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });

        // Determine how much time remains and action accordingly
        function worker() {

            $.ajaxSetup({cache: false});
            $.ajax({
                async: true,
                url: ajaxurl,
                data: {
                    'action': 'quiz_remain',
                },

                success:function(data) {

                    // Store remaining time
                    session = Number(data);

                    // If more than 5 min remaining make green
                    if (session > 300 ) {
                        $('#timerbox').addClass("green");
                    }
                    // If between 1 and 5 min make orange
                    else if (session <= 300 && session > 60) {
                        $('#timerbox').addClass("orange");
                    }

                    // If less than 1 min make red
                    else if (session>0 && session <= 60) {
                        $('#timerbox').addClass("red");
                    }

                    // If 0 time complete the quiz and alert user
                    else if (session==0) {

                        if (showAlert==true)
                        {
                            alert("Quiz time limit reached, your quiz will now be completed");
                            $('input[name="quiz_complete"]').trigger('click');
                            showAlert = false;
                        }
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                'action': 'quiz_end',
                            },
                            success:function(data) {
                                // This outputs the result of the ajax request
                                $('#timerbox').html(data);


                            },
                            error: function(errorThrown){
                                console.log(errorThrown);
                            }
                        });

                    }

                    // Make sure quiz is completed
                    else if (session<0) {
                        if (submitForm==true)
                        {
                            $('input[name="quiz_complete"]').trigger('click');
                            submitForm = false;
                        }
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                'action': 'quiz_end',
                            },
                            success:function(data) {
                                // This outputs the result of the ajax request
                                $('#timerbox').html(data);


                            },
                            error: function(errorThrown){
                                console.log(errorThrown);
                            }
                        });

                    };

                },
                error: function(errorThrown){
                    console.log(errorThrown);
                }
            });

        }

    }

    // Process quiz completion if complete quiz button is clicked
    $('input[name="quiz_complete"]').click(function() {
        $.ajax({
            url: ajaxurl,
            data: {
                'action': 'quiz_end',
            },
            success:function(data) {
                // This outputs the result of the ajax request
                $('#timerbox').html(data);

            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });

    });

});


