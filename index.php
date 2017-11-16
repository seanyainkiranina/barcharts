<?php

// Kickstart the framework
$f3=require('lib/base.php');
$f3->set('CACHE', 'folder=c:\\temp\\cache\\').
$f3->set('DEBUG', 1);
if ((float)PCRE_VERSION<7.9) {
    trigger_error('PCRE version is out of date');
}

// Load configuration
$f3->config('config.ini');

// Autoload classes
$f3->set('AUTOLOAD', array('classes/',function ($class) {
      return strtoupper($class);
}));


new Session();

// Load Default or saved symbol array
if (!is_array($f3->get('SESSION.symbols'))) {
    $quotesModel=new quotesmodel($f3->get('DATABASE_SERVER'), $f3->get('DATABASE'), $f3->get('USERNAME'), $f3->get('PASSWORD'));
    $f3->set('SESSION.symbols', $quotesModel->getWatchList());
}


// Default Route
$f3->route('GET /',
    function ($f3) {
        echo View::instance()->render('index.htm');
    }
);

// Route to load quotes
$f3->route('GET /quotes',
    function ($f3) {
        $quotesModel=new quotesmodel($f3->get('DATABASE_SERVER'), $f3->get('DATABASE'), $f3->get('USERNAME'), $f3->get('PASSWORD'));
        $return = new stdClass();
        $temp=$f3->get('SESSION.symbols');
        $return->data=$quotesModel->listQuotes($temp);
        echo json_encode($return);
    }
);

// Remove a quote from the table
$f3->route('GET /delquote/@symbol',
    function ($f3) {
                    $temp=$f3->get('SESSION.symbols');
                    $quotesModel=new quotesmodel($f3->get('DATABASE_SERVER'), $f3->get('DATABASE'), $f3->get('USERNAME'), $f3->get('PASSWORD'));
                    $key=array_search($f3->get('PARAMS.symbol'), $temp);
                        if (isset($temp[$key])){
                            unset($temp[$key]);
                        }
                        if (is_array($temp)){
                            sort($temp);
                            array_unique($temp);
                        }
            
                        $f3->set('SESSION.symbols', $temp);
                        $quotesModel->syncWatchList($temp);
                        echo "done";
    }
            
);

// Add a quote to the table
$f3->route('GET /quote/@symbol',
    function ($f3) {
        $return = new stdClass();
        $quotesModel=new quotesmodel($f3->get('DATABASE_SERVER'), $f3->get('DATABASE'), $f3->get('USERNAME'), $f3->get('PASSWORD'));
        $return->data=$quotesModel->searchQuotes($f3->get('PARAMS.symbol'));
        echo json_encode($return);
        
        if (is_array($return->data) && count($return->data)>0) {
                        $temp=$f3->get('SESSION.symbols');
            
                        array_push($temp, $f3->get('PARAMS.symbol'));
                        sort($temp);
                        array_unique($temp);
            
                        $f3->set('SESSION.symbols', $temp);
                        $quotesModel->syncWatchList($temp);
                        
        }
    }
        


);




$f3->run();
