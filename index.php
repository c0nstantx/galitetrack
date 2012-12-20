<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include 'Gatracking.php';
        $ga = new Galitetrack('UA-XXXXX-Y');
        
        $ga->pushParams = array(
            '_setVisitorCookieTimeout'=>63072000000,
            'trackPageLoadTime',
            '_setCustomVar'=>array(1, 'Section', 'Life & Style', 3),
        );
        
        $ga->trackElements = array(
            'imageClass'=>array(
                '_trackEvent',
                'button3',
                'clicked',
            ),
            'imageClass1'=>'_trackEvent',
        );
        
        echo '<pre>';
        echo htmlentities($ga->getTrackingCode());
        echo '</pre>';
        
        ?>
    </body>
</html>
