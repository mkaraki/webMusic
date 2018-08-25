<?php require(dirname(__FILE__).'/../../code/ui/update/update_library.php'); ?>
<html>
    <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function (){
            setInterval(function() {
                $.ajax({
                    type: 'GET',
                    url: '/ui/update/update_library_core.php?session_core=<?php echo $session_code_num ?>',
                    cache: false,
                    dataType: 'text',
                    success: function(data) {
                        document.getElementById('loading_scr').style.display = "none";
                        $('#realtime_stt').html(data);
                        scrollTop: $(document).height()
                    },
                    error: function() {
                        alert("Fail");
                    }
                });
            }, 3000);
        });
        </script>
    </head>
    <body>
        <div id="loading_scr" style="display:block">Loading</div>
        <div id="realtime_stt"></div>
    </body>
</html>